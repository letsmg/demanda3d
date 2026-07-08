<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Models\SecurityLog;
use App\Services\ImageModerationService;
use App\Services\ImageOptimizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ToolsController extends Controller
{
    /** Exibe a página de ferramentas do dashboard. */
    public function index(): Response
    {
        $sitemapPath = public_path('sitemap.xml');
        $sitemapDate = file_exists($sitemapPath)
            ? \Illuminate\Support\Carbon::createFromTimestamp(filemtime($sitemapPath))->format('d/m/Y H:i:s')
            : 'Nunca gerado';

        $productCount = \App\Models\Product::where('is_active', true)->whereNotNull('slug')->count();

        // Lista as imagens atuais do carrossel (home/)
        $heroImages = $this->getCurrentHeroImages();

        return Inertia::render('Tools/Index', [
            'sitemap' => [
                'exists' => file_exists($sitemapPath),
                'last_generated' => $sitemapDate,
                'product_count' => $productCount,
            ],
            'heroImages' => $heroImages,
        ]);
    }

    /** Gera o sitemap.xml. */
    public function generateSitemap(): RedirectResponse
    {
        \Artisan::call('sitemap:generate');

        return redirect()->route('tools.index')
            ->with('success', 'Sitemap gerado com sucesso!');
    }

    /**
     * Upload de imagens para o carrossel da home (admin only).
     *
     * Gera nomes sequenciais (3d-1.webp, 3d-2.webp, ...) e aplica
     * o pipeline de otimização (resize + compressão).
     */
    public function uploadHeroImages(Request $request, ImageOptimizationService $imageService, ImageModerationService $moderationService): RedirectResponse
    {
        Gate::authorize('admin.only');

        $request->validate([
            'image_name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'image_name.required' => 'Informe um nome SEO para a imagem.',
            'image_name.regex' => 'O nome deve conter apenas letras minúsculas, números e hífens (ex: impressao-3d-resina).',
            'image_name.max' => 'O nome deve ter no máximo 100 caracteres.',
            'images.required' => 'Selecione pelo menos uma imagem.',
            'images.*.image' => 'Cada arquivo deve ser uma imagem.',
            'images.*.mimes' => 'Formatos aceitos: JPG, PNG, WEBP.',
            'images.*.max' => 'Cada imagem deve ter no máximo 2MB.',
        ]);

        $imageName = $request->input('image_name');

        // Sanitiza o nome para SEO (slug amigável)
        $seoName = Str::slug($imageName);
        if (empty($seoName)) {
            return redirect()->route('tools.index')
                ->with('error', 'O nome da imagem gerou um slug vazio. Use palavras significativas.');
        }

        $uploaded = 0;
        $nextIndex = $this->getNextHeroImageIndex();

        foreach ($request->file('images') as $file) {
            // Moderação rigorosa — bloqueia QUALQUER conteúdo inadequado (adulto, violência, etc.)
            $moderationResult = $moderationService->analyze($file);

            // Para a home page, conteúdo ADULTO também é bloqueado (diferente de produtos)
            if (in_array($moderationResult['category']->value, ['adult', 'racy', 'violence', 'illegal'], true) || $moderationResult['status'] === 'rejected') {
                // Registra a tentativa de violação
                SecurityLog::create([
                    'tenant_id' => auth()->user()?->tenant_id,
                    'user_id' => auth()->id(),
                    'attempted_at' => now(),
                    'violation_type' => $moderationResult['details'],
                    'raw_response' => $moderationResult['raw_response'] ?? null,
                ]);

                return redirect()->route('tools.index')
                    ->with('error', "Imagem rejeitada: {$moderationResult['details']}. A home page não pode conter conteúdo inadequado.");
            }

            $filename = $imageName . ($nextIndex > 1 ? "-{$nextIndex}" : '');

            $imageService->processUpload($file, $filename);

            $nextIndex++;
            $uploaded++;
        }

        return redirect()->route('tools.index')
            ->with('success', "{$uploaded} imagem(ns) enviada(s) com nome '{$imageName}' e otimizada(s) com sucesso!");
    }

    /**
     * Remove uma imagem do carrossel (admin only).
     */
    /**
     * Recria todas as imagens otimizadas em imgs/home/ a partir dos originais em imgs/originais/.
     *
     * Fluxo:
     * 1. Limpa todo o conteúdo de imgs/home/
     * 2. Itera sobre cada arquivo em imgs/originais/
     * 3. Para cada arquivo, executa o pipeline de otimização (resize + compressão)
     * 4. O WelcomeController já lê imgs/home/ — os novos arquivos aparecem automaticamente
     */
    public function rebuildHeroImages(ImageOptimizationService $imageService): RedirectResponse
    {
        Gate::authorize('admin.only');

        $originalDir = 'imgs/originais';
        $homeDir = 'imgs/home';

        // 1. Limpa imgs/home/
        $existingHomeFiles = Storage::disk('public')->files($homeDir);
        foreach ($existingHomeFiles as $file) {
            Storage::disk('public')->delete($file);
        }

        // 2. Coleta todos os arquivos originais
        $originalFiles = Storage::disk('public')->files($originalDir);
        $imageExtensions = ['webp', 'jpg', 'jpeg', 'png', 'gif'];

        $originalImages = array_filter($originalFiles, function ($file) use ($imageExtensions) {
            return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $imageExtensions, true);
        });

        if (empty($originalImages)) {
            return redirect()->route('tools.index')
                ->with('error', 'Nenhuma imagem original encontrada em imgs/originais/.');
        }

        $processed = 0;
        $failed = 0;

        // 3. Processa cada original através do pipeline de otimização
        foreach ($originalImages as $originalPath) {
            try {
                $imageService->optimizeExisting($originalPath);
                $processed++;
            } catch (\Exception $e) {
                $failed++;
                \Illuminate\Support\Facades\Log::error('Falha ao recriar imagem da home.', [
                    'file' => $originalPath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('tools.index')
            ->with('success', "{$processed} imagem(ns) recriada(s) com sucesso em imgs/home/" . ($failed > 0 ? ". {$failed} falha(s)." : ''));
    }

    public function deleteHeroImage(Request $request): RedirectResponse
    {
        Gate::authorize('admin.only');

        $request->validate([
            'filename' => ['required', 'string'],
        ]);

        $filename = basename($request->input('filename'));
        $originalPath = 'imgs/originais/' . $filename;
        $homePath = 'imgs/home/' . $filename;
        $deleted = 0;

        if (Storage::disk('public')->exists($originalPath)) {
            Storage::disk('public')->delete($originalPath);
            $deleted++;
        }

        if (Storage::disk('public')->exists($homePath)) {
            Storage::disk('public')->delete($homePath);
            $deleted++;
        }

        return redirect()->route('tools.index')
            ->with('success', "Imagem removida com sucesso ({$deleted} arquivo(s) excluído(s)).");
    }

    /**
     * Retorna a lista de imagens atuais do carrossel com URLs absolutas.
     */
    private function getCurrentHeroImages(): array
    {
        $files = Storage::disk('public')->files('imgs/originais');
        $images = [];

        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['webp', 'jpg', 'jpeg', 'png', 'gif'], true)) {
                $images[] = [
                    'filename' => basename($file),
                    'url' => Storage::disk('public')->url($file),
                ];
            }
        }

        // Ordena pelo nome do arquivo
        usort($images, fn ($a, $b) => strnatcmp($a['filename'], $b['filename']));

        return $images;
    }

    /**
     * Determina o próximo índice sequencial (3d-{N}) baseado nos arquivos existentes.
     */
    private function getNextHeroImageIndex(): int
    {
        $maxIndex = 0;

        foreach (Storage::disk('public')->files('imgs/home') as $file) {
            $filename = basename($file);
            if (preg_match('/^3d-(\d+)\./', $filename, $matches)) {
                $index = (int) $matches[1];
                if ($index > $maxIndex) {
                    $maxIndex = $index;
                }
            }
        }

        return $maxIndex + 1;
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.