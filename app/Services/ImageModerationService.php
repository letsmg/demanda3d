<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

use App\Enums\ModerationRiskCategory;
use App\Models\Categoria;
use App\Models\Product;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Image as VisionImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Serviço de moderação inteligente de imagens.
 *
 * Realiza verificações paralelas de conteúdo via API de visão computacional
 * (ex: Google Cloud Vision SafeSearch). Classifica imagens em três categorias:
 * - SAFE: nenhum conteúdo sensível detectado
 * - ADULT: conteúdo adulto permitido → vincula categoria 'adulto'
 * - ILLEGAL: conteúdo ilegal → bloqueia upload, retorna erro 422
 */
class ImageModerationService
{
    /**
     * Analisa uma imagem e retorna o resultado da moderação.
     *
     * Se o produto já pertence à categoria 'adulto', a moderação SafeSearch é ignorada
     * para evitar rejeição indevida de conteúdo adulto legítimo.
     *
     * @return array{status: string, category: ModerationRiskCategory, details: string}
     */
    public function analyze(UploadedFile $image, ?Product $product = null): array
    {
        // Se o produto já está na categoria 'adulto', ignora moderação SafeSearch
        if ($product && $product->categorias()->where('slug', 'adulto')->exists()) {
            Log::info('Moderação ignorada: produto já está na categoria adulto.', [
                'product_id' => $product->id,
            ]);

            return [
                'status' => 'approved',
                'category' => ModerationRiskCategory::ADULT,
                'details' => 'Produto da categoria adulto — moderação SafeSearch ignorada.',
            ];
        }

        // Simula a chamada à API de visão computacional
        // Em produção, substituir por chamada real ao Google Cloud Vision
        $safeSearchResults = $this->callVisionApi($image);

        // Classifica o resultado
        $classification = ModerationRiskCategory::classify($safeSearchResults);

        // Determina o status baseado na classificação
        $status = match ($classification['category']) {
            ModerationRiskCategory::SAFE => 'approved',
            ModerationRiskCategory::ADULT => 'approved',
            ModerationRiskCategory::ILLEGAL => 'rejected',
        };

        Log::info('Resultado da moderação de imagem.', [
            'category' => $classification['category']->value,
            'details' => $classification['details'],
            'status' => $status,
            'product_id' => $product?->id,
        ]);

        return [
            'status' => $status,
            'category' => $classification['category'],
            'details' => $classification['details'],
        ];
    }

    /**
     * Processa o upload de imagem com moderação completa.
     *
     * @throws \RuntimeException Se conteúdo ilegal for detectado (422).
     * @return array{status: string, category: ModerationRiskCategory, adult_category_id: int|null}
     */
    public function moderateUpload(UploadedFile $image, ?Product $product = null): array
    {
        $result = $this->analyze($image, $product);

        if ($result['status'] === 'rejected') {
            Log::warning('Upload bloqueado: conteúdo ilegal detectado.', [
                'details' => $result['details'],
                'product_id' => $product?->id,
                'user_id' => auth()->id(),
            ]);

            throw new \RuntimeException(
                "Conteúdo não permitido: {$result['details']}",
                422
            );
        }

        // Se conteúdo adulto detectado, obtém o ID da categoria 'adulto'
        $adultCategoryId = null;
        if ($result['category'] === ModerationRiskCategory::ADULT) {
            $adultCategory = Categoria::where('slug', 'adulto')->first();
            $adultCategoryId = $adultCategory?->id;

            Log::info('Conteúdo adulto detectado — vinculando à categoria adulto.', [
                'product_id' => $product?->id,
                'categoria_id' => $adultCategoryId,
            ]);
        }

        return [
            'status' => $result['status'],
            'category' => $result['category'],
            'adult_category_id' => $adultCategoryId,
        ];
    }

    /**
     * Chama a API Google Cloud Vision para análise SafeSearch real.
     *
     * Utiliza a chave de service account configurada em GOOGLE_CLOUD_KEY_FILE.
     *
     * @return array<string, string> [category => likelihood]
     */
    private function callVisionApi(UploadedFile $image): array
    {
        $keyFilePath = config('services.google_cloud.key_file');

        if (!$keyFilePath || !file_exists($keyFilePath)) {
            Log::warning('Google Cloud Vision: chave de API não encontrada. Usando fallback UNKNOWN.', [
                'key_file' => $keyFilePath,
            ]);

            return $this->fallbackSafeResults();
        }

        try {
            $imageContent = file_get_contents($image->getRealPath());

            if ($imageContent === false) {
                Log::error('Google Cloud Vision: não foi possível ler o arquivo de imagem.');

                return $this->fallbackSafeResults();
            }

            $imageAnnotator = new ImageAnnotatorClient([
                'credentials' => $keyFilePath,
            ]);

            $visionImage = (new VisionImage())
                ->setContent($imageContent);

            $response = $imageAnnotator->safeSearchDetection($visionImage);
            $safeSearch = $response->getSafeSearchAnnotation();
            $imageAnnotator->close();

            if (!$safeSearch) {
                Log::warning('Google Cloud Vision: SafeSearch retornou vazio.');

                return $this->fallbackSafeResults();
            }

            $results = [
                'adult' => $safeSearch->getAdult() ?: 'UNKNOWN',
                'spoof' => $safeSearch->getSpoof() ?: 'UNKNOWN',
                'medical' => $safeSearch->getMedical() ?: 'UNKNOWN',
                'violence' => $safeSearch->getViolence() ?: 'UNKNOWN',
                'racy' => $safeSearch->getRacy() ?: 'UNKNOWN',
            ];

            Log::info('Google Cloud Vision: análise SafeSearch concluída.', $results);

            return $results;
        } catch (\Exception $e) {
            Log::error('Google Cloud Vision: erro na chamada da API.', [
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackSafeResults();
        }
    }

    /**
     * Resultados seguros de fallback quando a API não está disponível.
     */
    private function fallbackSafeResults(): array
    {
        return [
            'adult' => 'UNKNOWN',
            'spoof' => 'UNKNOWN',
            'medical' => 'UNKNOWN',
            'violence' => 'UNKNOWN',
            'racy' => 'UNKNOWN',
        ];
    }
}