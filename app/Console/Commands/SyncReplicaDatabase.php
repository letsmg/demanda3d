<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

class SyncReplicaDatabase extends Command
{
    protected $signature = 'db:sync-replica';
    protected $description = 'Configura replicação lógica entre master e réplica PostgreSQL';

    public function handle(): int
    {
        $split = env('DB_READ_WRITE_SPLIT', false);
        if (! $split) {
            $this->info('DB_READ_WRITE_SPLIT=false — réplica não está em uso. Nada a sincronizar.');
            return 0;
        }

        $masterConn = config('database.connections.pgsql.write');
        $replicaConn = config('database.connections.pgsql.read');
        $masterDsn = "pgsql:host={$masterConn['host'][0]};port={$masterConn['port']};dbname={$masterConn['database']}";
        $replicaDsn = "pgsql:host={$replicaConn['host'][0]};port={$replicaConn['port']};dbname={$replicaConn['database']}";

        try {
            $masterPdo = new PDO($masterDsn, $masterConn['username'], $masterConn['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $replicaPdo = new PDO($replicaDsn, $replicaConn['username'], $replicaConn['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (\Exception $e) {
            $this->error('❌ Falha ao conectar aos bancos: ' . $e->getMessage());
            return 1;
        }

        $this->info('⚡ Configurando replicação lógica PostgreSQL...');
        $this->line("  Master: {$masterConn['host'][0]}:{$masterConn['port']}/{$masterConn['database']}");
        $this->line("  Réplica: {$replicaConn['host'][0]}:{$replicaConn['port']}/{$replicaConn['database']}");

        try {
            // 0. Verifica e ativa wal_level=logical no master (necessário para replication)
            $walLevel = $masterPdo->query("SHOW wal_level")->fetchColumn();
            if ($walLevel !== 'logical') {
                $this->warn("  wal_level atual: {$walLevel} — ativando 'logical'...");
                $masterPdo->exec("ALTER SYSTEM SET wal_level = 'logical'");
                $masterPdo->exec("ALTER SYSTEM SET max_wal_senders = 10");
                $masterPdo->exec("ALTER SYSTEM SET max_replication_slots = 10");
                $masterPdo->exec("SELECT pg_reload_conf()");
                $this->info('  ✓ wal_level configurado como logical. Reinicie o container master se necessário.');
                $this->warn('  ⚠ Execute: docker compose restart demanda-psql-dev && aguarde 5s');
            } else {
                $this->info('  ✓ wal_level já está como logical');
            }

            // 1. Cria publication no master (se não existir)
            $masterPdo->exec("DROP PUBLICATION IF EXISTS demanda_pub");
            $masterPdo->exec("CREATE PUBLICATION demanda_pub FOR ALL TABLES");
            $this->info('  ✓ Publication "demanda_pub" criada no master');

            // 2. Remove subscription antiga na réplica (se existir)
            $replicaPdo->exec("DROP SUBSCRIPTION IF EXISTS demanda_sub");
            $this->info('  ✓ Subscription antiga removida');

            // 3. Dropa schema da réplica (vamos recriar com pg_dump completo)
            $this->info('⏳ Limpando schema da réplica...');
            try { $replicaPdo->exec("DROP SCHEMA public CASCADE"); } catch (\Exception $e) {}
            $replicaPdo->exec("CREATE SCHEMA public");
            $replicaPdo->exec("GRANT ALL ON SCHEMA public TO public");
            $this->info('  ✓ Schema limpo');

            // 4. Copia SCHEMA + DADOS do master para a réplica via pg_dump
            //    (mais confiável que copy_data da subscription para a carga inicial)
            $this->info('⏳ Copiando schema + dados do master para a réplica (pg_dump)...');

            $masterHostExt = $masterConn['host'][0];
            $masterPortExt = $masterConn['port'];
            $masterDbExt   = $masterConn['database'];
            $masterUserExt = $masterConn['username'];
            $masterPassExt = $masterConn['password'];

            $replicaHostExt = $replicaConn['host'][0];
            $replicaPortExt = $replicaConn['port'];
            $replicaDbExt   = $replicaConn['database'];
            $replicaUserExt = $replicaConn['username'];
            $replicaPassExt = $replicaConn['password'];

            $dumpCmd = sprintf(
                'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -d %s --no-owner --no-acl 2>&1 | PGPASSWORD=%s psql -h %s -p %s -U %s -d %s -q 2>&1',
                escapeshellarg($masterPassExt),
                escapeshellarg($masterHostExt),
                escapeshellarg($masterPortExt),
                escapeshellarg($masterUserExt),
                escapeshellarg($masterDbExt),
                escapeshellarg($replicaPassExt),
                escapeshellarg($replicaHostExt),
                escapeshellarg($replicaPortExt),
                escapeshellarg($replicaUserExt),
                escapeshellarg($replicaDbExt)
            );

            exec($dumpCmd, $dumpOutput, $dumpExitCode);
            if ($dumpExitCode !== 0) {
                $this->error('❌ Falha ao copiar dados:');
                foreach (array_slice($dumpOutput, -5) as $line) {
                    $this->line('  ' . $line);
                }
                return 1;
            }
            $this->info('  ✓ Dados copiados do master para a réplica');

            // Reconecta o PDO da réplica
            $replicaPdo = new PDO($replicaDsn, $replicaConn['username'], $replicaConn['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // 5. Cria subscription para replicação contínua (mudanças futuras)
            $masterHost = env('DOCKER_MASTER_HOST', 'demanda-psql-dev');
            $masterPort = 5432;
            $masterDb   = $masterConn['database'];
            $masterUser = $masterConn['username'];
            $connStr = "host={$masterHost} port={$masterPort} dbname={$masterDb} user={$masterUser}";
            $this->info('⏳ Criando subscription para replicação contínua...');
            $replicaPdo->exec("CREATE SUBSCRIPTION demanda_sub CONNECTION '{$connStr}' PUBLICATION demanda_pub WITH (copy_data = false)");
            $this->info('  ✓ Subscription "demanda_sub" criada');

            // 6. Verifica status
            sleep(2);
            $status = $replicaPdo->query("SELECT srsubstate FROM pg_catalog.pg_subscription_rel LIMIT 1")->fetchColumn();
            $this->info("  Estado: " . ($status === 'r' ? 'ready (streaming)' : ($status ?: 'active')));

        } catch (\Exception $e) {
            $this->error('❌ Erro na replicação: ' . $e->getMessage());
            return 1;
        }

        $this->info('✅ Replicação lógica ativa — alterações no master refletem na réplica em tempo real.');
        return 0;
    }
}