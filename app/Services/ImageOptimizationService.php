<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Spatie\ImageOptimizer\OptimizerChainFactory;

/**
 * Serviço dedicado à otimização de imagens.
 *
 * Responsável por redimensionar, converter formato e comprimir imagens
 * sem perda visual perceptível. Utilizado tanto pelo fluxo de upload
 * de produtos quanto pelo Artisan Command de processamento em lote.
 */
class ImageOptimizationService
{
    private const MAX_WIDTH = 1600;
    private const ORIGINAL_DISK = 'public';
    private const OPTIMIZED_DISK = 'public';
    private const ORIGINAL_DIR = 'imgs/originais';
    private const OPTIMIZED_DIR = 'imgs/home';
    private const PRODUCTS_DIR = 'imgs/products';


    /**
     * Processa upload de imagem de produto com paths por tenant e ID.
     *
     * Gera três versões:
     *   - original  (produtos/{tenant_id}/{product_id}/original/)
     *   - thumbnail (produtos/{tenant_id}/{product_id}/thumbnail/) 200x200
     *   - optimized (produtos/{tenant_id}/{product_id}/optimized/) webp 85%
     *
     * @return array{original_path: string, thumbnail_path: string, optimized_path: string}
     */
    public function processProductUpload(UploadedFile $file, int $tenantId, int $productId, string $slug = ''): array
    {
        $extension = $this->resolveExtension($file);
        $namePrefix = $slug ?: \Illuminate\Support\Str::uuid()->toString();
        $baseName = $namePrefix . '.' . $extension;

        $basePath = self::PRODUCTS_DIR . "/{$tenantId}/{$productId}";

        // 1. Salva original
        $originalPath = "{$basePath}/original/{$baseName}";
        Storage::disk('public')->put($originalPath, file_get_contents($file->getRealPath()));

        // 2. Gera thumbnail (200x200)
        $thumbnailPath = "{$basePath}/thumbnail/{$baseName}";
        $this->generateThumbnail($file, $thumbnailPath);

        // 3. Gera versão otimizada
        $optimizedPath = $this->optimizeAndSaveProduct($file, "{$basePath}/optimized", $baseName);

        return [
            'original_path' => $originalPath,
            'thumbnail_path' => $thumbnailPath,
            'optimized_path' => $optimizedPath,
        ];
    }

    /**
     * Gera uma miniatura 200x200 da imagem.
     */
    private function generateThumbnail(UploadedFile $file, string $targetPath): void
    {
        $manager = ImageManager::gd();
        $image = $manager->read($file->getRealPath());
        $image->cover(200, 200);

        $absoluteTarget = Storage::disk('public')->path($targetPath);
        $targetDir = dirname($absoluteTarget);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $tmpThumb = tempnam(sys_get_temp_dir(), 'thumb_') . '.webp';
        $image->toWebp(quality: 80)->save($tmpThumb);

        rename($tmpThumb, $absoluteTarget);
    }

    /**
     * Otimiza e salva a imagem de produto no diretório de destino.
     */
    private function optimizeAndSaveProduct(UploadedFile $file, string $basePath, string $filename): string
    {
        $manager = ImageManager::gd();
        $image = $manager->read($file->getRealPath());

        if ($image->width() > self::MAX_WIDTH) {
            $image->scale(width: self::MAX_WIDTH);
        }

        $targetFormat = 'webp';
        $outputFilename = pathinfo($filename, PATHINFO_FILENAME) . '.' . $targetFormat;
        $targetPath = "{$basePath}/{$outputFilename}";
        $absoluteTarget = Storage::disk('public')->path($targetPath);

        $targetDir = dirname($absoluteTarget);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $tmpOptimized = tempnam(sys_get_temp_dir(), 'opt_') . '.' . $targetFormat;
        $image->toWebp(quality: 85)->save($tmpOptimized);

        $optimizerChain = OptimizerChainFactory::create();
        $optimizerChain->optimize($tmpOptimized);

        rename($tmpOptimized, $absoluteTarget);

        return $targetPath;
    }

    /**
     * Processa um UploadedFile: salva original e gera versão otimizada.
     *
     * @param UploadedFile $file  Arquivo recebido do request.
     * @param string|null $filename  Nome base para o arquivo (sem extensão). Se null, gera UUID.
     * @return array{original_path: string, optimized_path: string, optimized_url: string}
     */
    public function processUpload(UploadedFile $file, ?string $filename = null): array
    {
        $name = $filename ?? \Illuminate\Support\Str::uuid()->toString();
        $extension = $this->resolveExtension($file);
        $baseName = "{$name}.{$extension}";

        // Salva original sem tratamento
        $originalPath = self::ORIGINAL_DIR . '/' . $baseName;
        Storage::disk(self::ORIGINAL_DISK)->put($originalPath, file_get_contents($file->getRealPath()));

        // Gera versão otimizada
        $optimizedPath = $this->optimizeAndSave($file, $baseName);

        return [
            'original_path' => $originalPath,
            'optimized_path' => $optimizedPath,
            'optimized_url' => Storage::disk(self::OPTIMIZED_DISK)->url($optimizedPath),
        ];
    }

    /**
     * Otimiza um arquivo já existente em disco (usado pelo batch command).
     *
     * @param string $relativePath Caminho relativo dentro do disco "public" (ex: imgs/originais/foto.jpg).
     * @return string Caminho relativo do arquivo otimizado salvo.
     */
    public function optimizeExisting(string $relativePath): string
    {
        $fullPath = Storage::disk(self::ORIGINAL_DISK)->path($relativePath);

        if (!file_exists($fullPath)) {
            throw new \RuntimeException("Arquivo não encontrado: {$fullPath}");
        }

        $baseName = basename($relativePath);
        $content = file_get_contents($fullPath);

        if ($content === false || empty($content)) {
            throw new \RuntimeException("Não foi possível ler o arquivo: {$fullPath}");
        }

        // Cria um UploadedFile temporário para reusar a lógica de redimensionamento
        $tmpPath = tempnam(sys_get_temp_dir(), 'img_') . '.' . pathinfo($baseName, PATHINFO_EXTENSION);
        file_put_contents($tmpPath, $content);

        $tmpFile = new UploadedFile(
            $tmpPath,
            $baseName,
            mime_content_type($tmpPath),
            null,
            true
        );

        $targetPath = $this->optimizeAndSave($tmpFile, $baseName);

        // Limpa o temporário
        @unlink($tmpPath);

        return $targetPath;
    }

    /**
     * Verifica se um arquivo otimizado correspondente já existe e é mais recente que o original.
     *
     * Como o otimizado pode ter extensão diferente do original (ex: jpg → webp),
     * a busca é feita pelo nome base (sem extensão) com qualquer extensão de imagem.
     */
    public function isAlreadyOptimized(string $relativePath): bool
    {
        $baseNameWithoutExt = pathinfo(basename($relativePath), PATHINFO_FILENAME);

        // Busca qualquer arquivo com o mesmo nome base em home/ (jpg, jpeg, png, webp, gif)
        $possibleExtensions = ['webp', 'jpg', 'jpeg', 'png', 'gif'];
        $foundPath = null;

        foreach ($possibleExtensions as $ext) {
            $candidate = self::OPTIMIZED_DIR . '/' . $baseNameWithoutExt . '.' . $ext;
            if (Storage::disk(self::OPTIMIZED_DISK)->exists($candidate)) {
                $foundPath = $candidate;
                break;
            }
        }

        if ($foundPath === null) {
            return false;
        }

        $originalTimestamp = Storage::disk(self::ORIGINAL_DISK)->lastModified($relativePath);
        $optimizedTimestamp = Storage::disk(self::OPTIMIZED_DISK)->lastModified($foundPath);

        return $optimizedTimestamp !== false
            && $originalTimestamp !== false
            && $optimizedTimestamp >= $originalTimestamp;
    }

    /**
     * Cria os diretórios necessários se não existirem.
     */
    public function ensureDirectories(): void
    {
        $originalDir = Storage::disk(self::ORIGINAL_DISK)->path(self::ORIGINAL_DIR);
        $optimizedDir = Storage::disk(self::OPTIMIZED_DISK)->path(self::OPTIMIZED_DIR);

        if (!is_dir($originalDir)) {
            mkdir($originalDir, 0755, true);
        }

        if (!is_dir($optimizedDir)) {
            mkdir($optimizedDir, 0755, true);
        }
    }

    /**
     * Retorna o path do diretório de originais relativo ao disco "public".
     */
    public function getOriginalDirectory(): string
    {
        return self::ORIGINAL_DIR;
    }

    /**
     * Retorna o path do diretório de otimizados relativo ao disco "public".
     */
    public function getOptimizedDirectory(): string
    {
        return self::OPTIMIZED_DIR;
    }

    /**
     * Núcleo da otimização: redimensiona e comprime a imagem.
     *
     * @param UploadedFile $file  Arquivo de imagem.
     * @param string $outputFilename  Nome do arquivo de saída (com extensão).
     * @return string Caminho relativo do arquivo otimizado.
     */
    private function optimizeAndSave(UploadedFile $file, string $outputFilename): string
    {
        $manager = ImageManager::gd();

        $image = $manager->read($file->getRealPath());

        // Redimensiona apenas se a largura for maior que MAX_WIDTH (sem upscale)
        if ($image->width() > self::MAX_WIDTH) {
            $image->scale(width: self::MAX_WIDTH);
        }

        // Converte para WebP se não for animação (mantém formato original para GIFs)
        $extension = strtolower(pathinfo($outputFilename, PATHINFO_EXTENSION));
        $targetFormat = $extension;

        // WebP é mais eficiente; converte jpg/png para webp
        if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            $targetFormat = 'webp';
        }

        // Se o formato de destino difere, ajusta o nome do arquivo
        if ($targetFormat !== $extension) {
            $outputFilename = pathinfo($outputFilename, PATHINFO_FILENAME) . '.' . $targetFormat;
        }

        $targetPath = self::OPTIMIZED_DIR . '/' . $outputFilename;
        $absoluteTarget = Storage::disk(self::OPTIMIZED_DISK)->path($targetPath);

        // Garante que o diretório de destino existe
        $targetDir = dirname($absoluteTarget);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Salva a imagem redimensionada em um arquivo temporário primeiro
        $tmpOptimized = tempnam(sys_get_temp_dir(), 'opt_') . '.' . $targetFormat;

        if ($targetFormat === 'webp') {
            $image->toWebp(quality: 85)->save($tmpOptimized);
        } elseif ($targetFormat === 'png') {
            $image->toPng()->save($tmpOptimized);
        } elseif ($targetFormat === 'gif') {
            // GIF: apenas copia sem redimensionar (preserva animação)
            copy($file->getRealPath(), $tmpOptimized);
        } else {
            $image->toJpeg(quality: 85)->save($tmpOptimized);
        }

        // Aplica compressão sem perda via spatie/image-optimizer
        $optimizerChain = OptimizerChainFactory::create();
        $optimizerChain->optimize($tmpOptimized);

        // Move para o destino final
        rename($tmpOptimized, $absoluteTarget);

        Log::info('Imagem otimizada com sucesso.', [
            'original' => $file->getClientOriginalName(),
            'target' => $outputFilename,
            'size_before' => $file->getSize(),
            'size_after' => filesize($absoluteTarget),
        ]);

        return $targetPath;
    }

    /**
     * Resolve a extensão de destino para o arquivo, priorizando webp para
     * formatos estáticos e mantendo o formato original para GIF animado.
     */
    private function resolveExtension(UploadedFile $file): string
    {
        $mime = $file->getMimeType();
        $originalExt = strtolower($file->getClientOriginalExtension());

        // Mantém GIF como está (possível animação)
        if ($mime === 'image/gif' || $originalExt === 'gif') {
            return 'gif';
        }

        // Converte JPG e PNG para WebP
        return 'webp';
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.