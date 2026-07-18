<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Serviço centralizado para geração de caminhos de armazenamento de imagens.
 *
 * Toda e qualquer referência a paths dentro de storage/app/public/imgs/
 * deve ser canalizada através das constantes e métodos desta classe.
 *
 * Estrutura:
 *   imgs/
 *   ├── site/                     ← assets do site (versionados)
 *   │   ├── home/original|optimized|thumbnail/
 *   │   ├── articles/{id}/original|optimized|thumbnail/
 *   │   └── carriers/{id}/original|optimized|thumbnail/
 *   └── tenants/                  ← dados de tenants (NÃO versionados)
 *       └── {tenant_id}/
 *           ├── products/{product_id}/original|optimized|thumbnail/
 *           ├── profile/original|optimized|thumbnail/
 *           ├── suppliers/{id}/original|optimized|thumbnail/
 *           └── carriers/{id}/original|optimized|thumbnail/
 */
final class ImageStorageService
{
    public const DISK = 'public';

    // Subdiretórios de qualidade (dentro de cada entidade)
    public const QUALITY_ORIGINAL  = 'original';
    public const QUALITY_OPTIMIZED = 'optimized';
    public const QUALITY_THUMBNAIL = 'thumbnail';

    // ─── Site (versionado) ────────────────────────────────────

    private const SITE_HOME     = 'imgs/site/home';
    private const SITE_ARTICLES = 'imgs/site/articles';
    private const SITE_CARRIERS = 'imgs/site/carriers';

    // ─── Tenants (NÃO versionado) ─────────────────────────────

    private const TENANTS           = 'imgs/tenants';
    private const TENANT_PRODUCTS   = '{tenant_id}/products/{product_id}';
    private const TENANT_PROFILE    = '{tenant_id}/profile';
    private const TENANT_SUPPLIERS  = '{tenant_id}/suppliers/{supplier_id}';
    private const TENANT_CARRIERS   = '{tenant_id}/carriers/{carrier_id}';

    // ──────────────────────────────────────────────────────────
    //  Site Home (carrossel da landing page)
    // ──────────────────────────────────────────────────────────

    /**
     * Diretório de originais do carrossel da home (legado: imgs/originals).
     */
    public static function siteHomeOriginalDir(): string
    {
        return self::SITE_HOME . '/' . self::QUALITY_ORIGINAL;
    }

    /**
     * Diretório de otimizados do carrossel da home (legado: imgs/home).
     */
    public static function siteHomeOptimizedDir(): string
    {
        return self::SITE_HOME . '/' . self::QUALITY_OPTIMIZED;
    }

    /**
     * Diretório de thumbnails do carrossel da home.
     */
    public static function siteHomeThumbnailDir(): string
    {
        return self::SITE_HOME . '/' . self::QUALITY_THUMBNAIL;
    }

    /**
     * Todos os subdiretórios de qualidade da home do site.
     *
     * @return string[]
     */
    public static function siteHomeAllDirs(): array
    {
        return [
            self::siteHomeOriginalDir(),
            self::siteHomeOptimizedDir(),
            self::siteHomeThumbnailDir(),
        ];
    }

    // ──────────────────────────────────────────────────────────
    //  Site Articles
    // ──────────────────────────────────────────────────────────

    public static function siteArticleDir(int $articleId, string $quality = self::QUALITY_ORIGINAL): string
    {
        return self::SITE_ARTICLES . "/{$articleId}/{$quality}";
    }

    public static function siteArticleAllDirs(int $articleId): array
    {
        return [
            self::siteArticleDir($articleId, self::QUALITY_ORIGINAL),
            self::siteArticleDir($articleId, self::QUALITY_OPTIMIZED),
            self::siteArticleDir($articleId, self::QUALITY_THUMBNAIL),
        ];
    }

    // ──────────────────────────────────────────────────────────
    //  Site Carriers (global, sem tenant)
    // ──────────────────────────────────────────────────────────

    public static function siteCarrierDir(int $carrierId, string $quality = self::QUALITY_ORIGINAL): string
    {
        return self::SITE_CARRIERS . "/{$carrierId}/{$quality}";
    }

    public static function siteCarrierAllDirs(int $carrierId): array
    {
        return [
            self::siteCarrierDir($carrierId, self::QUALITY_ORIGINAL),
            self::siteCarrierDir($carrierId, self::QUALITY_OPTIMIZED),
            self::siteCarrierDir($carrierId, self::QUALITY_THUMBNAIL),
        ];
    }

    // ──────────────────────────────────────────────────────────
    //  Tenant Products
    // ──────────────────────────────────────────────────────────

    /**
     * Retorna o caminho base para produtos de um tenant.
     *
     * Ex.: imgs/tenants/5/products/42
     */
    public static function tenantProductBase(int $tenantId, int $productId): string
    {
        return self::TENANTS . '/' . str_replace(
            ['{tenant_id}', '{product_id}'],
            [(string) $tenantId, (string) $productId],
            self::TENANT_PRODUCTS
        );
    }

    /**
     * Retorna o caminho completo com qualidade para um produto.
     *
     * Ex.: imgs/tenants/5/products/42/original
     */
    public static function tenantProductDir(int $tenantId, int $productId, string $quality = self::QUALITY_ORIGINAL): string
    {
        return self::tenantProductBase($tenantId, $productId) . "/{$quality}";
    }

    /**
     * Todos os subdiretórios de qualidade para um produto.
     *
     * @return string[]
     */
    public static function tenantProductAllDirs(int $tenantId, int $productId): array
    {
        return [
            self::tenantProductDir($tenantId, $productId, self::QUALITY_ORIGINAL),
            self::tenantProductDir($tenantId, $productId, self::QUALITY_OPTIMIZED),
            self::tenantProductDir($tenantId, $productId, self::QUALITY_THUMBNAIL),
        ];
    }

    // ──────────────────────────────────────────────────────────
    //  Tenant Profile
    // ──────────────────────────────────────────────────────────

    public static function tenantProfileBase(int $tenantId): string
    {
        return self::TENANTS . '/' . str_replace(
            '{tenant_id}',
            (string) $tenantId,
            self::TENANT_PROFILE
        );
    }

    public static function tenantProfileDir(int $tenantId, string $quality = self::QUALITY_ORIGINAL): string
    {
        return self::tenantProfileBase($tenantId) . "/{$quality}";
    }

    /**
     * @return string[]
     */
    public static function tenantProfileAllDirs(int $tenantId): array
    {
        return [
            self::tenantProfileDir($tenantId, self::QUALITY_ORIGINAL),
            self::tenantProfileDir($tenantId, self::QUALITY_OPTIMIZED),
            self::tenantProfileDir($tenantId, self::QUALITY_THUMBNAIL),
        ];
    }

    // ──────────────────────────────────────────────────────────
    //  Tenant Suppliers
    // ──────────────────────────────────────────────────────────

    public static function tenantSupplierDir(int $tenantId, int $supplierId, string $quality = self::QUALITY_ORIGINAL): string
    {
        return self::TENANTS . '/' . str_replace(
            ['{tenant_id}', '{supplier_id}'],
            [(string) $tenantId, (string) $supplierId],
            self::TENANT_SUPPLIERS
        ) . "/{$quality}";
    }

    /**
     * @return string[]
     */
    public static function tenantSupplierAllDirs(int $tenantId, int $supplierId): array
    {
        return [
            self::tenantSupplierDir($tenantId, $supplierId, self::QUALITY_ORIGINAL),
            self::tenantSupplierDir($tenantId, $supplierId, self::QUALITY_OPTIMIZED),
            self::tenantSupplierDir($tenantId, $supplierId, self::QUALITY_THUMBNAIL),
        ];
    }

    // ──────────────────────────────────────────────────────────
    //  Tenant Carriers
    // ──────────────────────────────────────────────────────────

    public static function tenantCarrierDir(int $tenantId, int $carrierId, string $quality = self::QUALITY_ORIGINAL): string
    {
        return self::TENANTS . '/' . str_replace(
            ['{tenant_id}', '{carrier_id}'],
            [(string) $tenantId, (string) $carrierId],
            self::TENANT_CARRIERS
        ) . "/{$quality}";
    }

    /**
     * @return string[]
     */
    public static function tenantCarrierAllDirs(int $tenantId, int $carrierId): array
    {
        return [
            self::tenantCarrierDir($tenantId, $carrierId, self::QUALITY_ORIGINAL),
            self::tenantCarrierDir($tenantId, $carrierId, self::QUALITY_OPTIMIZED),
            self::tenantCarrierDir($tenantId, $carrierId, self::QUALITY_THUMBNAIL),
        ];
    }

    // ──────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────

    /**
     * Lista todas as entidades que possuem as 3 subpastas de qualidade.
     * Útil para comandos de migração e verificação.
     *
     * @return array<string, string[]>
     */
    public static function allSiteDirs(): array
    {
        return [
            'site_home' => self::siteHomeAllDirs(),
        ];
    }

    /**
     * Garante que todos os diretórios base existam no disco.
     *
     * Cria as pastas raiz: imgs/site, imgs/tenants.
     */
    public static function ensureBaseDirectories(): void
    {
        $disk = Storage::disk(self::DISK);

        $baseDirs = [
            'imgs/site',
            'imgs/tenants',
        ];

        foreach ($baseDirs as $dir) {
            $absolute = $disk->path($dir);
            if (! is_dir($absolute)) {
                mkdir($absolute, 0755, true);
            }
        }
    }

    /**
     * Garante que um conjunto de diretórios exista no disco.
     *
     * @param string[] $dirs
     */
    public static function ensureDirectories(array $dirs): void
    {
        $disk = Storage::disk(self::DISK);

        foreach ($dirs as $dir) {
            $absolute = $disk->path($dir);
            if (! is_dir($absolute)) {
                mkdir($absolute, 0755, true);
            }
        }
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.