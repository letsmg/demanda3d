<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Console\Commands;

use App\Services\ImageOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OptimizeImagesBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize-batch {--force : Força o reprocessamento mesmo que o otimizado já exista e seja mais recente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa todas as imagens de storage/app/public/imgs/originals, gerando versões otimizadas em storage/app/public/imgs/home';

    /**
     * Execute the console command.
     */
    public function handle(ImageOptimizationService $service): int
    {
        $this->info('=== Pipeline de Otimização de Imagens ===');
        $this->newLine();

        $force = (bool) $this->option('force');
        if ($force) {
            $this->warn('Modo --force ativado: todos os arquivos serão reprocessados.');
            $this->newLine();
        }

        // Garante que os diretórios existem
        $service->ensureDirectories();

        $originalDir = $service->getOriginalDirectory();

        // Lista todos os arquivos em originais/
        $files = Storage::disk('public')->files($originalDir);

        if (empty($files)) {
            $this->info("Nenhum arquivo encontrado em {$originalDir}.");
            return self::SUCCESS;
        }

        // Filtra apenas arquivos de imagem
        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $imageFiles = array_filter($files, function (string $path) use ($imageExtensions) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            return in_array($ext, $imageExtensions, true);
        });

        if (empty($imageFiles)) {
            $this->info("Nenhuma imagem encontrada em {$originalDir}.");
            return self::SUCCESS;
        }

        $this->info("Encontradas " . count($imageFiles) . " imagem(ns) para processar.");
        $this->newLine();

        $processed = 0;
        $skipped = 0;
        $failed = 0;

        $progressBar = $this->output->createProgressBar(count($imageFiles));
        $progressBar->start();

        foreach ($imageFiles as $relativePath) {
            try {
                // Verifica idempotência (a menos que --force)
                if (!$force && $service->isAlreadyOptimized($relativePath)) {
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                $service->optimizeExisting($relativePath);
                $processed++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('Falha ao processar imagem no batch.', [
                    'file' => $relativePath,
                    'error' => $e->getMessage(),
                ]);
                $this->newLine();
                $this->error("  Falha: {$relativePath} — {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Resumo final
        $this->info('=== Resumo ===');
        $this->info("  Processados: {$processed}");
        $this->info("  Pulados (já otimizados): {$skipped}");
        if ($failed > 0) {
            $this->error("  Falhas: {$failed}");
        } else {
            $this->info('  Falhas: 0');
        }

        $this->newLine();
        $this->info('Pipeline concluído.');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.