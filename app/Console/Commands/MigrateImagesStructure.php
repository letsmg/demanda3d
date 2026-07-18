<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Console\Commands;

use App\Models\ProductImage;
use App\Models\Tenant;
use App\Services\ImageStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Migra a estrutura de imagens do padrão antigo para o novo.
 *
 * Estrutura antiga:
 *   imgs/originals/                        → imgs/site/home/original/
 *   imgs/home/                             → imgs/site/home/optimized/
 *   imgs/{tenant_id}/products/{product_id}/ → imgs/tenants/{tenant_id}/products/{product_id}/original/
 *   imgs/{tenant_id}/profile/              → imgs/tenants/{tenant_id}/profile/original/
 *
 * Uso:
 *   php artisan images:migrate-structure --dry-run   (simulação)
 *   php artisan images:migrate-structure             (execução real)
 */
class MigrateImagesStructure extends Command
{
    protected $signature = 'images:migrate-structure
                            {--dry-run : Simula a migração sem executar nenhuma operação de arquivo ou banco}';

    protected $description = 'Migra a estrutura de imagens do padrão antigo (imgs/originals, imgs/home, imgs/{id}/...) para o novo padrão (imgs/site/..., imgs/tenants/...)';

    private bool $dryRun = false;

    /** @var array{source: string, dest: string}[] */
    private array $fileMoves = [];

    /** @var array{table: string, id: int, field: string, old: string, new: string}[] */
    private array $dbUpdates = [];

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('=== MODO DRY-RUN (SIMULAÇÃO) ===');
            $this->warn('Nenhuma alteração será feita em arquivos ou banco de dados.');
            $this->newLine();
        } else {
            $this->info('=== Migração de Estrutura de Imagens ===');
            $this->newLine();
            $this->warn('Certifique-se de ter um backup de storage/app/public/imgs/ antes de continuar!');
            if (! $this->confirm('Deseja prosseguir com a migração?', false)) {
                $this->info('Migração cancelada.');

                return self::SUCCESS;
            }
            $this->newLine();
        }

        // 1. Garante diretórios base
        $this->ensureBaseDirectories();

        // 2. Planeja migração do site (home)
        $this->planSiteHomeMigration();

        // 3. Planeja migração dos tenants
        $this->planTenantsMigration();

        // 4. Planeja atualização de registros no banco (ProductImage)
        $this->planDatabaseUpdates();

        // ─── Execução ─────────────────────────────────────────

        if ($this->dryRun) {
            $this->printDryRunSummary();
            return self::SUCCESS;
        }

        // Executa as movimentações de arquivo
        $this->executeFileMoves();

        // Executa as atualizações de banco
        $this->executeDatabaseUpdates();

        // 5. Recria o storage:link
        $this->recreateStorageLink();

        $this->newLine();
        $this->info('=== Migração concluída com sucesso! ===');
        $this->info('Execute "php artisan config:clear" se necessário.');

        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────────────────
    //  Planejamento
    // ──────────────────────────────────────────────────────────

    private function ensureBaseDirectories(): void
    {
        $this->info('Criando diretórios base...');

        ImageStorageService::ensureBaseDirectories();
        ImageStorageService::ensureDirectories(ImageStorageService::siteHomeAllDirs());

        $this->info('  ✓ imgs/site/, imgs/tenants/');
        $this->newLine();
    }

    /**
     * Planeja a migração das imagens do site (home).
     */
    private function planSiteHomeMigration(): void
    {
        $this->info('Planejando migração do site (home)...');

        $disk = Storage::disk(ImageStorageService::DISK);
        $basePath = $disk->path('');

        // originais → site/home/original
        $oldOriginalDir = 'imgs/originals';
        $newOriginalDir = ImageStorageService::siteHomeOriginalDir();

        $this->planDirectoryMove($oldOriginalDir, $newOriginalDir, $basePath, 'originais do carrossel');

        // home → site/home/optimized
        $oldHomeDir = 'imgs/home';
        $newHomeDir = ImageStorageService::siteHomeOptimizedDir();

        $this->planDirectoryMove($oldHomeDir, $newHomeDir, $basePath, 'otimizados do carrossel');

        $this->newLine();
    }

    /**
     * Planeja a migração dos diretórios de tenants.
     */
    private function planTenantsMigration(): void
    {
        $this->info('Planejando migração de tenants...');

        $disk = Storage::disk(ImageStorageService::DISK);
        $basePath = $disk->path('');

        $imgsDir = $basePath . '/imgs';
        if (! is_dir($imgsDir)) {
            $this->info('  Nenhum diretório imgs/ encontrado.');
            return;
        }

        // Escaneia diretórios numéricos (tenant IDs) em imgs/
        $entries = scandir($imgsDir);
        $tenantIds = [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            // Apenas diretórios com nome numérico (IDs de tenant)
            if (is_dir($imgsDir . '/' . $entry) && ctype_digit($entry)) {
                $tenantIds[] = (int) $entry;
            }
        }

        if (empty($tenantIds)) {
            $this->info('  Nenhum diretório de tenant encontrado em imgs/.');
            return;
        }

        $this->info('  Encontrados ' . count($tenantIds) . ' tenant(s): ' . implode(', ', $tenantIds));

        foreach ($tenantIds as $tenantId) {
            $oldTenantBase = "imgs/{$tenantId}";
            $oldTenantAbs  = $basePath . '/' . $oldTenantBase;

            // products
            $oldProductsDir = $oldTenantBase . '/products';
            $oldProductsAbs = $basePath . '/' . $oldProductsDir;

            if (is_dir($oldProductsAbs)) {
                $productDirs = scandir($oldProductsAbs);
                foreach ($productDirs as $productDir) {
                    if ($productDir === '.' || $productDir === '..') {
                        continue;
                    }
                    $oldProductPath = $oldProductsDir . '/' . $productDir;
                    $oldProductAbs  = $basePath . '/' . $oldProductPath;

                    if (is_dir($oldProductAbs) && ctype_digit($productDir)) {
                        $productId     = (int) $productDir;
                        $newProductDir = ImageStorageService::tenantProductDir($tenantId, $productId, ImageStorageService::QUALITY_ORIGINAL);

                        // Garante que o diretório de destino existe
                        if (! $this->dryRun) {
                            ImageStorageService::ensureDirectories(ImageStorageService::tenantProductAllDirs($tenantId, $productId));
                        }

                        $this->planDirectoryMove($oldProductPath, $newProductDir, $basePath, "produto {$productId} do tenant {$tenantId}");
                    }
                }
            }

            // profile
            $oldProfileDir = $oldTenantBase . '/profile';
            $oldProfileAbs = $basePath . '/' . $oldProfileDir;

            if (is_dir($oldProfileAbs)) {
                $newProfileDir = ImageStorageService::tenantProfileDir($tenantId, ImageStorageService::QUALITY_ORIGINAL);

                if (! $this->dryRun) {
                    ImageStorageService::ensureDirectories(ImageStorageService::tenantProfileAllDirs($tenantId));
                }

                $this->planDirectoryMove($oldProfileDir, $newProfileDir, $basePath, "perfil do tenant {$tenantId}");
            }
        }

        $this->newLine();
    }

    /**
     * Planeja a movimentação de todos os arquivos de um diretório para outro.
     */
    private function planDirectoryMove(string $oldDir, string $newDir, string $basePath, string $label): void
    {
        $oldAbs = $basePath . '/' . $oldDir;

        if (! is_dir($oldAbs)) {
            $this->line("  ⏭ {$label}: diretório antigo não existe ({$oldDir}) — pulando.");
            return;
        }

        $files = scandir($oldAbs);
        $count = 0;

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $oldFilePath = $oldDir . '/' . $file;
            $oldFileAbs  = $basePath . '/' . $oldFilePath;

            // Apenas arquivos (não subdiretórios)
            if (is_file($oldFileAbs)) {
                $newFilePath = $newDir . '/' . $file;

                $this->fileMoves[] = [
                    'source' => $oldFilePath,
                    'dest'   => $newFilePath,
                    'label'  => $label,
                ];
                $count++;
            }
        }

        if ($count > 0) {
            $this->line("  ✓ {$label}: {$count} arquivo(s) — {$oldDir} → {$newDir}");
        } else {
            $this->line("  ⏭ {$label}: diretório vazio — pulando.");
        }
    }

    /**
     * Planeja a atualização de registros no banco de dados.
     *
     * ProductImage.path, original_path, thumbnail_path:
     *   imgs/{tenant_id}/products/{product_id}/... → imgs/tenants/{tenant_id}/products/{product_id}/original/...
     */
    private function planDatabaseUpdates(): void
    {
        $this->info('Planejando atualizações de banco de dados...');

        $images = ProductImage::all();
        $count  = 0;

        foreach ($images as $image) {
            $fields = ['path', 'original_path', 'thumbnail_path'];

            foreach ($fields as $field) {
                $oldPath = $image->{$field};

                if (empty($oldPath)) {
                    continue;
                }

                // Verifica se o path usa o padrão antigo: imgs/{tenant_id}/products/{product_id}/...
                if (preg_match('#^imgs/(\d+)/products/(\d+)/(.+)$#', $oldPath, $matches)) {
                    $tenantIdStr  = $matches[1];
                    $productIdStr = $matches[2];
                    $filename     = $matches[3];

                    // Novo path: imgs/tenants/{tenant_id}/products/{product_id}/original/{filename}
                    $newPath = ImageStorageService::tenantProductDir((int) $tenantIdStr, (int) $productIdStr, ImageStorageService::QUALITY_ORIGINAL) . '/' . $filename;

                    $this->dbUpdates[] = [
                        'table' => 'product_images',
                        'id'    => $image->id,
                        'field' => $field,
                        'old'   => $oldPath,
                        'new'   => $newPath,
                    ];
                    $count++;
                }
                // Verifica se o path usa o padrão antigo: imgs/{tenant_id}/profile/...
                elseif (preg_match('#^imgs/(\d+)/profile/(.+)$#', $oldPath, $matches)) {
                    $tenantIdStr = $matches[1];
                    $filename    = $matches[2];

                    // Novo path: imgs/tenants/{tenant_id}/profile/original/{filename}
                    $newPath = ImageStorageService::tenantProfileDir((int) $tenantIdStr, ImageStorageService::QUALITY_ORIGINAL) . '/' . $filename;

                    $this->dbUpdates[] = [
                        'table' => 'product_images',
                        'id'    => $image->id,
                        'field' => $field,
                        'old'   => $oldPath,
                        'new'   => $newPath,
                    ];
                    $count++;
                }
            }
        }

        // Também verifica Tenant.logo_path e Tenant.banner_path
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            foreach (['logo_path', 'banner_path'] as $field) {
                $oldPath = $tenant->{$field};

                if (empty($oldPath)) {
                    continue;
                }

                if (preg_match('#^imgs/(\d+)/profile/(.+)$#', $oldPath, $matches)) {
                    $tenantIdStr = $matches[1];
                    $filename    = $matches[2];

                    // Novo path: imgs/tenants/{tenant_id}/profile/optimized/{filename}
                    $newPath = ImageStorageService::tenantProfileDir((int) $tenantIdStr, ImageStorageService::QUALITY_OPTIMIZED) . '/' . $filename;

                    $this->dbUpdates[] = [
                        'table' => 'tenants',
                        'id'    => $tenant->id,
                        'field' => $field,
                        'old'   => $oldPath,
                        'new'   => $newPath,
                    ];
                    $count++;
                }
            }
        }

        if ($count > 0) {
            $this->line("  ✓ {$count} registro(s) para atualizar no banco.");
        } else {
            $this->line('  ⏭ Nenhum registro de banco precisa de atualização.');
        }

        $this->newLine();
    }

    // ──────────────────────────────────────────────────────────
    //  Execução (não dry-run)
    // ──────────────────────────────────────────────────────────

    private function executeFileMoves(): void
    {
        if (empty($this->fileMoves)) {
            $this->info('Nenhum arquivo para mover.');
            return;
        }

        $this->info('Executando movimentação de ' . count($this->fileMoves) . ' arquivo(s)...');

        $disk     = Storage::disk(ImageStorageService::DISK);
        $basePath = $disk->path('');
        $moved    = 0;
        $errors   = 0;

        $progressBar = $this->output->createProgressBar(count($this->fileMoves));
        $progressBar->start();

        foreach ($this->fileMoves as $move) {
            try {
                $sourceAbs = $basePath . '/' . $move['source'];
                $destAbs   = $basePath . '/' . $move['dest'];

                // Garante que o diretório de destino existe
                $destDir = dirname($destAbs);
                if (! is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }

                // Move o arquivo (rename é atômico no mesmo filesystem)
                if (file_exists($sourceAbs)) {
                    rename($sourceAbs, $destAbs);
                    $moved++;
                } else {
                    $this->newLine();
                    $this->warn("  Arquivo não encontrado: {$move['source']}");
                    $errors++;
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("  Erro ao mover {$move['source']}: {$e->getMessage()}");
                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("  Movidos: {$moved} | Erros: {$errors}");

        // Tenta remover diretórios antigos vazios
        $this->cleanupEmptyDirs();
    }

    /**
     * Remove diretórios antigos que ficaram vazios após a migração.
     */
    private function cleanupEmptyDirs(): void
    {
        $disk     = Storage::disk(ImageStorageService::DISK);
        $basePath = $disk->path('');

        $oldDirs = [
            'imgs/originals',
            'imgs/home',
        ];

        // Adiciona diretórios numéricos de tenants
        $imgsDir = $basePath . '/imgs';
        if (is_dir($imgsDir)) {
            foreach (scandir($imgsDir) as $entry) {
                if (ctype_digit($entry)) {
                    $oldDirs[] = "imgs/{$entry}/products";
                    $oldDirs[] = "imgs/{$entry}/profile";
                    $oldDirs[] = "imgs/{$entry}";
                }
            }
        }

        // Adiciona o próprio imgs/ se estiver vazio (além de site/ e tenants/)
        $oldDirs[] = 'imgs';

        foreach ($oldDirs as $dir) {
            $absPath = $basePath . '/' . $dir;
            if (is_dir($absPath)) {
                // Verifica se o diretório está vazio (ignorando . e ..)
                $contents = array_diff(scandir($absPath), ['.', '..']);
                if (empty($contents)) {
                    @rmdir($absPath);
                    $this->line("  🧹 Diretório vazio removido: {$dir}");
                }
            }
        }
    }

    private function executeDatabaseUpdates(): void
    {
        if (empty($this->dbUpdates)) {
            $this->info('Nenhum registro de banco para atualizar.');
            return;
        }

        $this->info('Atualizando ' . count($this->dbUpdates) . ' registro(s) no banco de dados...');

        DB::transaction(function () {
            $updated = 0;

            foreach ($this->dbUpdates as $update) {
                DB::table($update['table'])
                    ->where('id', $update['id'])
                    ->update([$update['field'] => $update['new']]);
                $updated++;
            }

            $this->info("  {$updated} registro(s) atualizado(s).");
        });
    }

    private function recreateStorageLink(): void
    {
        $this->info('Recriando link simbólico do storage...');

        $publicStorage = public_path('storage');

        // Remove o link antigo se existir
        if (is_link($publicStorage)) {
            unlink($publicStorage);
        } elseif (is_dir($publicStorage)) {
            // Se for um diretório real (improvável), avisa
            $this->warn('  public/storage é um diretório real, não um link. Verifique manualmente.');
        }

        // Executa o comando nativo
        $this->call('storage:link');

        $this->info('  ✓ storage:link recriado.');
        $this->newLine();
    }

    // ──────────────────────────────────────────────────────────
    //  Dry-run output
    // ──────────────────────────────────────────────────────────

    private function printDryRunSummary(): void
    {
        $this->newLine();
        $this->info('=== RESUMO DA SIMULAÇÃO ===');
        $this->newLine();

        if (! empty($this->fileMoves)) {
            $this->info('📁 Arquivos que seriam movidos (' . count($this->fileMoves) . '):');
            $this->newLine();

            // Agrupa por label
            $grouped = [];
            foreach ($this->fileMoves as $move) {
                $grouped[$move['label']][] = $move;
            }

            foreach ($grouped as $label => $moves) {
                $this->line("  {$label} (" . count($moves) . ' arquivo(s)):');
                foreach (array_slice($moves, 0, 5) as $move) {
                    $this->line("    {$move['source']}");
                    $this->line("    → {$move['dest']}");
                }
                if (count($moves) > 5) {
                    $this->line('    ... e mais ' . (count($moves) - 5) . ' arquivo(s)');
                }
                $this->newLine();
            }
        } else {
            $this->info('📁 Nenhum arquivo para mover.');
            $this->newLine();
        }

        if (! empty($this->dbUpdates)) {
            $this->info('🗄️ Registros de banco que seriam atualizados (' . count($this->dbUpdates) . '):');
            $this->newLine();

            foreach (array_slice($this->dbUpdates, 0, 10) as $update) {
                $this->line("  {$update['table']}.{$update['id']} :: {$update['field']}");
                $this->line("    {$update['old']}");
                $this->line("    → {$update['new']}");
            }
            if (count($this->dbUpdates) > 10) {
                $this->line('  ... e mais ' . (count($this->dbUpdates) - 10) . ' registro(s)');
            }
            $this->newLine();
        } else {
            $this->info('🗄️ Nenhum registro de banco para atualizar.');
            $this->newLine();
        }

        $this->info('Para executar a migração real, rode:');
        $this->info('  php artisan images:migrate-structure');
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.