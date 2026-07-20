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
 *
 * Todos os paths de armazenamento são delegados ao ImageStorageService.
 */
class ImageOptimizationService
{
    private const MAX_WIDTH = 1600;

    // ──────────────────────────────────────────────────────────
    //  Upload de Produto (tenant)
    // ──────────────────────────────────────────────────────────

    /**
     * Processa upload de imagem de produto com paths por tenant e ID.
     *
     * Gera três versões:
     *   - original  (tenants/{tenant_id}/products/{product_id}/original/)
     *   - thumbnail (tenants/{tenant_id}/products/{product_id}/thumbnail/) 200x200
     *   - optimized (tenants/{tenant_id}/products/{product_id}/optimized/) webp 85%
     *
     * @return array{original_path: string, thumbnail_path: string, optimized_path: string}
     */
    public function processProductUpload(UploadedFile $file, int $tenantId, int $productId, string $slug = ''): array
    {
        $extension = $this->resolveExtension($file);
        $namePrefix = $slug ?: \Illuminate\Support\Str::uuid()->toString();
        $baseName = $namePrefix . '.' . $extension;

        $basePath   = ImageStorageService::tenantProductBase($tenantId, $productId);
        $originalDir = ImageStorageService::tenantProductDir($tenantId, $productId, ImageStorageService::QUALITY_ORIGINAL);
        $thumbDir    = ImageStorageService::tenantProductDir($tenantId, $productId, ImageStorageService::QUALITY_THUMBNAIL);
        $optimizedDir = ImageStorageService::tenantProductDir($tenantId, $productId, ImageStorageService::QUALITY_OPTIMIZED);

        // Garante que os diretórios existem
        ImageStorageService::ensureDirectories([$originalDir, $thumbDir, $optimizedDir]);

        // 1. Salva original
        $originalPath = "{$originalDir}/{$baseName}";
        Storage::disk(ImageStorageService::DISK)->put($originalPath, file_get_contents($file->getRealPath()));

        // 2. Gera thumbnail (200x200)
        $thumbnailPath = "{$thumbDir}/{$baseName}";
        $this->generateThumbnail($file, $thumbnailPath);

        // 3. Gera versão otimizada
        $optimizedPath = $this->optimizeAndSaveTo($file, $optimizedDir, $baseName);

        return [
            'original_path'  => $originalPath,
            'thumbnail_path' => $thumbnailPath,
            'optimized_path' => $optimizedPath,
        ];
    }

    // ──────────────────────────────────────────────────────────
    //  Upload de Perfil do Tenant
    // ──────────────────────────────────────────────────────────

    /**
     * Processa upload de logo ou banner para perfil do tenant.
     *
     * @param UploadedFile $file   Arquivo de imagem
     * @param int          $tenantId
     * @param string       $type   'logo' ou 'banner'
     *
     * @return string Caminho relativo do arquivo otimizado
     */
    public function processTenantProfileUpload(UploadedFile $file, int $tenantId, string $type): string
    {
        $extension = $this->resolveExtension($file);
        $baseName  = $type . '.' . $extension;

        $targetDir = ImageStorageService::tenantProfileDir($tenantId, ImageStorageService::QUALITY_OPTIMIZED);

        ImageStorageService::ensureDirectories([$targetDir]);

        return $this->optimizeAndSaveTo($file, $targetDir, $baseName);
    }

    // ──────────────────────────────────────────────────────────
    //  Upload Genérico (Site Home — carrossel)
    // ──────────────────────────────────────────────────────────

    /**
     * Processa um UploadedFile: salva original e gera versão otimizada.
     *
     * @param UploadedFile $file  Arquivo recebido do request.
     * @param string|null $filename  Nome base para o arquivo (sem extensão). Se null, gera UUID.
     * @return array{original_path: string, optimized_path: string, optimized_url: string}
     */
    public function processUpload(UploadedFile $file, ?string $filename = null): array
    {
        $name      = $filename ?? \Illuminate\Support\Str::uuid()->toString();
        $extension = $this->resolveExtension($file);
        $baseName  = "{$name}.{$extension}";

        $originalDir  = ImageStorageService::siteHomeOriginalDir();
        $optimizedDir = ImageStorageService::siteHomeOptimizedDir();

        ImageStorageService::ensureDirectories([$originalDir, $optimizedDir]);

        // Salva original sem tratamento
        $originalPath = $originalDir . '/' . $baseName;
        Storage::disk(ImageStorageService::DISK)->put($originalPath, file_get_contents($file->getRealPath()));

        // Gera versão otimizada
        $optimizedPath = $this->optimizeAndSaveTo($file, $optimizedDir, $baseName);

        return [
            'original_path'  => $originalPath,
            'optimized_path' => $optimizedPath,
            'optimized_url'  => Storage::disk(ImageStorageService::DISK)->url($optimizedPath),
        ];
    }

    // ──────────────────────────────────────────────────────────
    //  Batch: reprocessar existentes
    // ──────────────────────────────────────────────────────────

    /**
     * Otimiza um arquivo já existente em disco (usado pelo batch command).
     *
     * @param string $relativePath Caminho relativo dentro do disco "public".
     * @return string Caminho relativo do arquivo otimizado salvo.
     */
    public function optimizeExisting(string $relativePath): string
    {
        $fullPath = Storage::disk(ImageStorageService::DISK)->path($relativePath);

        if (!file_exists($fullPath)) {
            throw new \RuntimeException("Arquivo não encontrado: {$fullPath}");
        }

        $baseName = basename($relativePath);
        $content  = file_get_contents($fullPath);

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

        $targetDir = ImageStorageService::siteHomeOptimizedDir();
        ImageStorageService::ensureDirectories([$targetDir]);

        $targetPath = $this->optimizeAndSaveTo($tmpFile, $targetDir, $baseName);

        // Limpa o temporário
        @unlink($tmpPath);

        return $targetPath;
    }

    // ──────────────────────────────────────────────────────────
    //  Idempotência
    // ──────────────────────────────────────────────────────────

    /**
     * Verifica se um arquivo otimizado correspondente já existe e é mais recente que o original.
     *
     * Como o otimizado pode ter extensão diferente do original (ex: jpg → webp),
     * a busca é feita pelo nome base (sem extensão) com qualquer extensão de imagem.
     */
    public function isAlreadyOptimized(string $relativePath): bool
    {
        $baseNameWithoutExt = pathinfo(basename($relativePath), PATHINFO_FILENAME);

        $optimizedDir     = ImageStorageService::siteHomeOptimizedDir();
        $possibleExtensions = ['webp', 'jpg', 'jpeg', 'png', 'gif'];
        $foundPath          = null;

        foreach ($possibleExtensions as $ext) {
            $candidate = $optimizedDir . '/' . $baseNameWithoutExt . '.' . $ext;
            if (Storage::disk(ImageStorageService::DISK)->exists($candidate)) {
                $foundPath = $candidate;
                break;
            }
        }

        if ($foundPath === null) {
            return false;
        }

        $originalTimestamp  = Storage::disk(ImageStorageService::DISK)->lastModified($relativePath);
        $optimizedTimestamp = Storage::disk(ImageStorageService::DISK)->lastModified($foundPath);

        return $optimizedTimestamp !== false
            && $originalTimestamp !== false
            && $optimizedTimestamp >= $originalTimestamp;
    }

    // ──────────────────────────────────────────────────────────
    //  Diretórios (compatibilidade com batch command)
    // ──────────────────────────────────────────────────────────

    /**
     * Cria os diretórios necessários se não existirem.
     */
    public function ensureDirectories(): void
    {
        ImageStorageService::ensureDirectories(ImageStorageService::siteHomeAllDirs());
    }

    /**
     * Retorna o path do diretório de originais relativo ao disco "public".
     */
    public function getOriginalDirectory(): string
    {
        return ImageStorageService::siteHomeOriginalDir();
    }

    /**
     * Retorna o path do diretório de otimizados relativo ao disco "public".
     */
    public function getOptimizedDirectory(): string
    {
        return ImageStorageService::siteHomeOptimizedDir();
    }

    // ──────────────────────────────────────────────────────────
    //  Métodos privados de processamento
    // ──────────────────────────────────────────────────────────

    /**
     * Gera uma miniatura 200x200 da imagem.
     */
    private function generateThumbnail(UploadedFile $file, string $targetPath): void
    {
        $manager = ImageManager::gd();
        $image   = $manager->read($file->getRealPath());
        $image->cover(200, 200);

        $absoluteTarget = Storage::disk(ImageStorageService::DISK)->path($targetPath);
        $targetDir      = dirname($absoluteTarget);

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $tmpThumb = tempnam(sys_get_temp_dir(), 'thumb_') . '.webp';
        $image->toWebp(quality: 80)->save($tmpThumb);

        rename($tmpThumb, $absoluteTarget);
    }

    /**
     * Núcleo da otimização: redimensiona, converte para webp e comprime.
     *
     * @param UploadedFile $file       Arquivo de imagem.
     * @param string       $targetDir  Diretório de destino relativo ao disco "public".
     * @param string       $filename   Nome do arquivo de saída (com extensão).
     * @return string Caminho relativo do arquivo otimizado.
     */
    private function optimizeAndSaveTo(UploadedFile $file, string $targetDir, string $filename): string
    {
        $manager = ImageManager::gd();
        $image   = $manager->read($file->getRealPath());

        // Redimensiona apenas se a largura for maior que MAX_WIDTH (sem upscale)
        if ($image->width() > self::MAX_WIDTH) {
            $image->scale(width: self::MAX_WIDTH);
        }

        // Converte para WebP se não for animação
        $extension     = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $targetFormat  = $extension;

        // WebP é mais eficiente; converte jpg/png para webp
        if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            $targetFormat = 'webp';
        }

        // Se o formato de destino difere, ajusta o nome do arquivo
        if ($targetFormat !== $extension) {
            $filename = pathinfo($filename, PATHINFO_FILENAME) . '.' . $targetFormat;
        }

        $targetPath     = $targetDir . '/' . $filename;
        $absoluteTarget = Storage::disk(ImageStorageService::DISK)->path($targetPath);

        // Garante que o diretório de destino existe
        $targetDirAbsolute = dirname($absoluteTarget);
        if (!is_dir($targetDirAbsolute)) {
            mkdir($targetDirAbsolute, 0755, true);
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
            'original'    => $file->getClientOriginalName(),
            'target'      => $filename,
            'size_before' => $file->getSize(),
            'size_after'  => filesize($absoluteTarget),
        ]);

        return $targetPath;
    }

    /**
     * Resolve a extensão de destino para o arquivo, priorizando webp para
     * formatos estáticos e mantendo o formato original para GIF animado.
     */
    private function resolveExtension(UploadedFile $file): string
    {
        $mime        = $file->getMimeType();
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