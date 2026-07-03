<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageModerationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    private readonly ImageModerationService $imageModerationService;

    public function __construct(
        ?ImageModerationService $imageModerationService = null,
    ) {
        $this->imageModerationService = $imageModerationService ?? app(ImageModerationService::class);
    }

    public function list(int $perPage = 15, array $filters = [])
    {
        $query = Product::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('sale_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('sale_price', '<=', $filters['max_price']);
        }

        $sortField = $filters['sort'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        $allowedSorts = ['name', 'sale_price', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Product
    {
        $data['tenant_id'] = auth()->user()->tenant->id;

        $categorias = $data['categorias'] ?? [];
        unset($data['categorias']);

        // Auto-generate SEO fields based on name, description and categories
        $data = $this->autoGenerateSeoFields($data, null);

        $product = Product::create($data);

        if (!empty($categorias)) {
            $product->categorias()->sync($categorias);
        }

        $this->syncImages($product, $data['images'] ?? []);

        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        $categorias = $data['categorias'] ?? null;
        unset($data['categorias']);

        // Auto-generate SEO fields based on name, description and categories
        $data = $this->autoGenerateSeoFields($data, $product);

        $product->update($data);

        if ($categorias !== null) {
            $product->categorias()->sync($categorias);
        }

        if (isset($data['images'])) {
            $this->syncImages($product, $data['images']);
        }

        return $product;
    }

    public function delete(Product $product): bool
    {
        // Delete all images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        return $product->delete();
    }

    /**
     * Get all active products for the public store.
     * Uses withoutGlobalScopes so all tenants' products are visible.
     */
    public function listActiveForStore(array $filters = [], bool $canViewAdult = false)
    {
        $searchTerm = $filters['search'] ?? '';
        $minPrice = $filters['min_price'] ?? null;
        $maxPrice = $filters['max_price'] ?? null;
        $sortField = $filters['sort'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        // Cache apenas quando NÃO há filtro de categoria, preço ou sort customizado
        $useCache = !empty($searchTerm) && strlen($searchTerm) >= 3
                    && empty($minPrice) && empty($maxPrice)
                    && empty($filters['categoria'])
                    && $sortField === 'name' && $sortDir === 'asc';

        if ($useCache) {
            $cacheKey = 'store:search:' . hash('sha256', strtolower(trim($searchTerm)));
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }
        }

        // --- Build query ---
        $query = Product::withoutGlobalScopes()
            ->where('is_active', true)
            ->whereHas('tenant', function ($q) {
                $q->whereHas('user', function ($sq) {
                    $sq->whereHas('vendorCarriers', function ($vc) {
                        $vc->where('status', 'approved');
                    });
                });
            })
            ->with(['images', 'tenant.user', 'categorias']);

        // Filtro de conteúdo adulto
        if (! $canViewAdult) {
            $query->withoutAdultCategories();
        }

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ilike', "%{$searchTerm}%")
                  ->orWhere('description', 'ilike', "%{$searchTerm}%");
            });
        }

        if (!empty($minPrice)) {
            $query->where('sale_price', '>=', (float) $minPrice);
        }

        if (!empty($maxPrice)) {
            $query->where('sale_price', '<=', (float) $maxPrice);
        }

        // Filtro por categoria
        if (!empty($filters['categoria'])) {
            $query->whereHas('categorias', function ($q) use ($filters) {
                $q->where('slug', $filters['categoria']);
            });
        }

        $allowedSorts = ['name', 'sale_price', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $results = $query->get();

        // --- Store in cache for future searches ---
        if ($useCache && isset($cacheKey)) {
            Cache::put($cacheKey, $results, now()->addMinutes(10));
        }

        return $results;
    }

    /**
     * Lista produtos ativos para a API pública.
     * Aplica filtro de conteúdo adulto baseado na permissão do usuário.
     */
    public function listActiveForApi(array $filters, bool $canViewAdult = false)
    {
        $searchTerm = $filters['search'] ?? '';
        $minPrice = $filters['min_price'] ?? null;
        $maxPrice = $filters['max_price'] ?? null;
        $sortField = $filters['sort'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $categoriaSlug = $filters['categoria'] ?? null;

        $query = Product::withoutGlobalScopes()
            ->where('is_active', true)
            ->with(['images', 'categorias']);

        // Filtro de conteúdo adulto
        if (!$canViewAdult) {
            $query->withoutAdultCategories();
        }

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ilike', "%{$searchTerm}%")
                  ->orWhere('description', 'ilike', "%{$searchTerm}%");
            });
        }

        if (!empty($minPrice)) {
            $query->where('sale_price', '>=', (float) $minPrice);
        }

        if (!empty($maxPrice)) {
            $query->where('sale_price', '<=', (float) $maxPrice);
        }

        if (!empty($categoriaSlug)) {
            $query->whereHas('categorias', function ($q) use ($categoriaSlug) {
                $q->where('slug', $categoriaSlug);
            });
        }

        $allowedSorts = ['name', 'sale_price', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        return $query->get();
    }

    /**
     * Determina se a moderação automática de imagem deve ser ignorada.
     *
     * Se o produto pertence à categoria 'adulto', a moderação (ex: Google SafeSearch)
     * deve ser ignorada, pois o conteúdo adulto é permitido nessa categoria específica.
     */
    public function shouldSkipImageModeration(Product $product): bool
    {
        return $product->categorias()->where('slug', 'adulto')->exists();
    }

    /**
     * Gera automaticamente campos SEO com base no nome, descrição e categorias do produto.
     *
     * - Só preenche campos que não foram explicitamente informados pelo usuário.
     * - No update, recalcula apenas campos cujo valor atual é igual ao auto-gerado anteriormente
     *   ou que estão vazios, evitando sobrescrever personalizações manuais do usuário.
     *
     * @param  array         $data        Dados recebidos do formulário
     * @param  Product|null  $existing    Produto existente (null para criação)
     * @return array                      Dados com campos SEO auto-gerados
     */
    private function autoGenerateSeoFields(array $data, ?Product $existing): array
    {
        $name = $data['name'] ?? ($existing?->name ?? '');
        $description = $data['description'] ?? ($existing?->description ?? '');

        // Resolve categorias: prioriza as enviadas no form, depois as existentes no model
        $categoriaIds = $data['categorias'] ?? null;
        $categoriaNames = [];

        if ($categoriaIds !== null && is_array($categoriaIds)) {
            $categoriaNames = \App\Models\Categoria::whereIn('id', $categoriaIds)->pluck('name')->toArray();
        } elseif ($existing) {
            $categoriaNames = $existing->categorias()->pluck('name')->toArray();
        }

        $categoriaString = implode(', ', $categoriaNames);

        // Helper: determina se o campo deve ser auto-gerado
        $shouldGenerate = function (string $field) use ($data, $existing): bool {
            // Se o usuário enviou o campo explicitamente (não vazio), respeita o valor
            if (!empty($data[$field])) {
                return false;
            }
            // Se está criando, gera sempre que vazio
            if (!$existing) {
                return true;
            }
            // Se está atualizando e o campo está vazio no request, gera

            return true;
        };

        // meta_title: usa o nome do produto (máx. 120 chars)
        if ($shouldGenerate('meta_title')) {
            $data['meta_title'] = mb_substr(trim($name), 0, 120);
        }

        // meta_description: usa a descrição do produto (máx. 320 chars)
        if ($shouldGenerate('meta_description')) {
            $cleanDescription = trim(strip_tags($description));
            $data['meta_description'] = mb_substr($cleanDescription ?: $name, 0, 320);
        }

        // meta_keywords: gera a partir do nome + categorias
        if ($shouldGenerate('meta_keywords')) {
            $keywords = $this->generateKeywords($name, $categoriaString, $data);
            $data['meta_keywords'] = mb_substr($keywords, 0, 255);
        }

        // og_image: usa a primeira imagem do produto, se existir
        if ($shouldGenerate('og_image')) {
            if ($existing && $firstImage = $existing->firstImage()) {
                $data['og_image'] = url('storage/' . $firstImage->path);
            }
        }

        // schema_markup: gera JSON-LD Product estruturado
        if ($shouldGenerate('schema_markup')) {
            $data['schema_markup'] = $this->generateSchemaMarkup($data, $existing, $categoriaString);
        }

        // google_tag_manager: gera script GTM com dataLayer para o produto
        if ($shouldGenerate('google_tag_manager')) {
            $data['google_tag_manager'] = $this->generateGtmScript($data, $existing);
        }

        return $data;
    }

    /**
     * Gera keywords relevantes a partir do nome do produto e categorias.
     * Extrai palavras significativas, combina com a categoria e adiciona
     * termos de cauda longa relevantes para impressão 3D.
     */
    private function generateKeywords(string $name, string $categoriaString, array $data = []): string
    {
        $keywords = [];

        // Adiciona palavras do nome do produto (remove palavras muito curtas)
        $nameWords = explode(' ', strtolower(trim($name)));
        foreach ($nameWords as $word) {
            $clean = preg_replace('/[^a-zà-ú0-9]/', '', $word);
            if (mb_strlen($clean) >= 3 && !in_array($clean, ['com', 'para', 'que', 'dos', 'das', 'uma', 'são'])) {
                $keywords[] = $clean;
            }
        }

        // Adiciona o nome completo como keyword
        $keywords[] = strtolower(trim($name));

        // Adiciona categorias
        if (!empty($categoriaString)) {
            foreach (explode(', ', strtolower($categoriaString)) as $cat) {
                $keywords[] = trim($cat);
                $keywords[] = trim($cat) . ' impressão 3d';
            }
        }

        // Adiciona termos genéricos relevantes para o nicho
        $genericTerms = [
            'impressão 3d',
            'produto 3d',
            'marketplace 3d',
            'impressão sob demanda',
            'peça 3d personalizada',
            'filamento ' . strtolower($data['material_type'] ?? ''),
            'prototipagem 3d',
        ];

        foreach ($genericTerms as $term) {
            $term = trim($term);
            if (!empty($term) && $term !== 'filamento ') {
                $keywords[] = $term;
            }
        }

        // Remove duplicatas, palavras vazias e limita
        $keywords = array_unique(array_filter($keywords));

        return implode(', ', $keywords);
    }

    /**
     * Gera schema markup JSON-LD para Product (schema.org).
     *
     * Inclui: nome, descrição, imagem, preço, disponibilidade, categorias,
     * marca (tenant), SKU e dimensões quando disponíveis.
     */
    private function generateSchemaMarkup(array $data, ?Product $existing, string $categoriaString): string
    {
        $name = $data['name'] ?? ($existing?->name ?? 'Produto');
        $description = trim(strip_tags($data['description'] ?? ($existing?->description ?? '')));
        $salePrice = $data['sale_price'] ?? ($existing?->sale_price ?? 0);
        $slug = $data['slug'] ?? ($existing?->slug ?? '');
        $tenantName = '';
        $imageUrl = '';

        if ($existing) {
            $tenantName = $existing->tenant?->display_name ?? '';
            if ($firstImage = $existing->firstImage()) {
                $imageUrl = url('storage/' . $firstImage->path);
            }
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $name,
            'description' => mb_substr($description ?: $name, 0, 5000),
            'sku' => $slug ?: 'PROD-' . ($existing?->id ?? 'new'),
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) $salePrice,
                'priceCurrency' => 'BRL',
                'availability' => 'https://schema.org/InStock',
                'url' => $slug ? route('store.detail', ['slug' => $slug]) : '',
                'priceValidUntil' => now()->addYear()->format('Y-m-d'),
            ],
        ];

        if (!empty($imageUrl)) {
            $schema['image'] = $imageUrl;
        }

        if (!empty($tenantName)) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name' => $tenantName,
            ];
        }

        if (!empty($categoriaString)) {
            $schema['category'] = $categoriaString;
        }

        // Adiciona dimensões se disponíveis
        $height = $data['height'] ?? ($existing?->height ?? null);
        $width = $data['width'] ?? ($existing?->width ?? null);
        $weight = $data['approximate_weight'] ?? ($existing?->approximate_weight ?? null);

        if ($height || $width || $weight) {
            $schema['additionalProperty'] = [];
            if ($height) {
                $schema['additionalProperty'][] = [
                    '@type' => 'PropertyValue',
                    'name' => 'Altura',
                    'value' => $height . ' mm',
                ];
            }
            if ($width) {
                $schema['additionalProperty'][] = [
                    '@type' => 'PropertyValue',
                    'name' => 'Largura',
                    'value' => $width . ' mm',
                ];
            }
            if ($weight) {
                $schema['additionalProperty'][] = [
                    '@type' => 'PropertyValue',
                    'name' => 'Peso Aproximado',
                    'value' => $weight . ' g',
                ];
            }
        }

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Gera script de Google Tag Manager com dataLayer para o produto.
     *
     * Configura evento de page_view com dados do produto para remarketing
     * e trackeamento de e-commerce no Google Analytics 4.
     */
    private function generateGtmScript(array $data, ?Product $existing): string
    {
        $name = $data['name'] ?? ($existing?->name ?? 'Produto');
        $salePrice = $data['sale_price'] ?? ($existing?->sale_price ?? 0);
        $slug = $data['slug'] ?? ($existing?->slug ?? '');
        $productId = $existing?->id ?? '';

        $dataLayer = [
            'event' => 'product_detail_view',
            'ecommerce' => [
                'detail' => [
                    'products' => [[
                        'id' => (string) $productId,
                        'name' => $name,
                        'price' => (string) $salePrice,
                        'variant' => $slug,
                    ]],
                ],
            ],
        ];

        $dataLayerJson = json_encode($dataLayer, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return <<<HTML
<!-- Google Tag Manager -->
<script>
  window.dataLayer = window.dataLayer || [];
  dataLayer.push({$dataLayerJson});
</script>
<!-- End Google Tag Manager -->
HTML;
    }

    /**
     * Sync product images: delete removed ones, upload new ones.
     * Integra moderação inteligente de imagem via ImageModerationService.
     *
     * @throws \RuntimeException Se conteúdo ilegal for detectado (422).
     */
    private function syncImages(Product $product, array $images): void
    {
        $order = 0;
        $maxImages = 5;

        // Remove excess images if more than allowed
        $currentCount = $product->images()->count();
        $remainingSlots = $maxImages - $currentCount;

        if ($remainingSlots <= 0) {
            return;
        }

        foreach ($images as $image) {
            if ($image instanceof UploadedFile && $order < $remainingSlots) {
                // Validate max size (4MB)
                if ($image->getSize() > 4 * 1024 * 1024) {
                    continue;
                }

                // Moderação inteligente da imagem
                try {
                    $moderationResult = $this->imageModerationService->moderateUpload($image, $product);

                    // Se conteúdo adulto detectado, vincula à categoria 'adulto'
                    if ($moderationResult['adult_category_id']) {
                        $currentCategorias = $product->categorias()->pluck('categoria_id')->toArray();
                        if (!in_array($moderationResult['adult_category_id'], $currentCategorias, true)) {
                            $currentCategorias[] = $moderationResult['adult_category_id'];
                            $product->categorias()->sync($currentCategorias);
                            Log::info('Categoria adulto vinculada automaticamente via moderação.', [
                                'product_id' => $product->id,
                            ]);
                        }
                    }

                    // Se classificação incerta, marca produto como pendente de revisão
                    if ($moderationResult['status'] === 'approved' && str_contains($moderationResult['category']->value, 'safe')) {
                        // Verifica se houve incerteza na classificação
                        if (str_contains($product->moderation_status ?? '', 'pending')) {
                            $product->update(['moderation_status' => 'pending_review']);
                        }
                    }
                } catch (\RuntimeException $e) {
                    // Conteúdo ilegal — relança a exceção para o controller tratar
                    throw $e;
                }

                $path = $image->store("imgs/products/{$product->tenant_id}", 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'order' => $currentCount + $order++,
                ]);
            }
        }
    }
}