#!/bin/sh
set -e

# ==============================================================================
# 00-init-master.sh — Inicialização do PostgreSQL Master (Demanda3D)
#
# Executado UMA VEZ pelo docker-entrypoint-initdb.d na criação do volume.
# Não é reexecutado em reinícios normais do container.
#
# Pré-requisitos (via .env.docker):
#   POSTGRES_USER, POSTGRES_PASSWORD  — superuser do cluster
#   REPL_USER, REPL_PASSWORD          — usuário de replicação
#   REPL_SLOT                         — nome do slot de replicação físico
# ==============================================================================

PGUSER="${POSTGRES_USER:?POSTGRES_USER must be set}"
PGPASSWORD="${POSTGRES_PASSWORD:?POSTGRES_PASSWORD must be set}"

echo "=== [Master Init] 1/3: Configurando pg_hba.conf ==="

cat > "$PGDATA/pg_hba.conf" << 'EOF'
# Conexões locais (socket Unix) — superuser e demais usuários
local   all             all                                     md5

# Conexões TCP locais (localhost)
host    all             all             127.0.0.1/32            md5
host    all             all             ::1/128                 md5

# Conexões TCP de redes internas Docker
host    all             all             172.16.0.0/12           md5

# Replicação — socket Unix e redes Docker
local   replication     all                                     md5
host    replication     all             172.16.0.0/12           md5
EOF

echo "=== [Master Init] 2/3: Aplicando configurações do sistema ==="

PGPASSWORD="$PGPASSWORD" psql -U "$PGUSER" -d postgres << 'EOSQL'
-- ===========================================================================
-- WAL e Replicação
-- ===========================================================================
ALTER SYSTEM SET listen_addresses    = '*';
ALTER SYSTEM SET wal_level           = 'replica';
ALTER SYSTEM SET max_wal_senders     = 10;
ALTER SYSTEM SET wal_keep_size       = '128MB';
ALTER SYSTEM SET max_replication_slots = 5;
ALTER SYSTEM SET hot_standby         = on;
ALTER SYSTEM SET wal_log_hints       = on;

-- 'local' (não 'on'): confirma transações sem esperar a réplica.
-- Com 'on', qualquer instabilidade da réplica suspende todos os writes
-- do site até ela reconectar — inaceitável em produção.
ALTER SYSTEM SET synchronous_commit  = 'local';

-- ===========================================================================
-- Memória — valores conservadores para servidor de 1GB compartilhado.
-- shared_buffers > 64MB causa FATAL no Alpine Docker sem --shm-size
-- configurado explicitamente no compose. Ajustar quando migrar para
-- servidor com maior capacidade.
-- ===========================================================================
ALTER SYSTEM SET shared_buffers      = '64MB';
ALTER SYSTEM SET effective_cache_size = '256MB';
ALTER SYSTEM SET maintenance_work_mem = '32MB';
ALTER SYSTEM SET work_mem            = '2MB';

-- ===========================================================================
-- Outras configurações
-- ===========================================================================
ALTER SYSTEM SET random_page_cost    = 1.1;
ALTER SYSTEM SET timezone            = 'America/Sao_Paulo';
ALTER SYSTEM SET log_timezone        = 'America/Sao_Paulo';
ALTER SYSTEM SET datestyle           = 'iso, dmy';

SELECT pg_reload_conf();
EOSQL

echo "=== [Master Init] Criando usuário de replicação ==="

PGPASSWORD="$PGPASSWORD" psql -U "$PGUSER" -d postgres \
    -v REPL_USER="${REPL_USER:?REPL_USER must be set}" \
    -v REPL_PASSWORD="${REPL_PASSWORD:?REPL_PASSWORD must be set}" << 'EOSQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT FROM pg_catalog.pg_roles WHERE rolname = :'REPL_USER'
    ) THEN
        EXECUTE format(
            'CREATE ROLE %I WITH REPLICATION LOGIN PASSWORD %L',
            :'REPL_USER',
            :'REPL_PASSWORD'
        );
        RAISE NOTICE 'Usuário de replicação % criado.', :'REPL_USER';
    ELSE
        RAISE NOTICE 'Usuário de replicação % já existe, pulando criação.', :'REPL_USER';
    END IF;
END
$$;
EOSQL

echo "=== [Master Init] 3/3: Criando slot de replicação ==="

PGPASSWORD="$PGPASSWORD" psql -U "$PGUSER" -d postgres \
    -v REPL_SLOT="${REPL_SLOT:?REPL_SLOT must be set}" << 'EOSQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_replication_slots WHERE slot_name = :'REPL_SLOT'
    ) THEN
        PERFORM pg_create_physical_replication_slot(:'REPL_SLOT', true);
        RAISE NOTICE 'Slot de replicação % criado.', :'REPL_SLOT';
    ELSE
        RAISE NOTICE 'Slot de replicação % já existe, pulando criação.', :'REPL_SLOT';
    END IF;
END
$$;
EOSQL

echo "=== [Master Init] Criando banco de dados de réplica lógica (demanda_db_dev_repl) ==="

PGPASSWORD="$PGPASSWORD" psql -U "$PGUSER" -d postgres << 'EOSQL'
-- Cria o segundo banco lógico dentro do mesmo container para simular
-- arquitetura de réplica sem consumir RAM de um segundo container.
DO $$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_database WHERE datname = 'demanda_db_dev_repl') THEN
        CREATE DATABASE demanda_db_dev_repl;
        RAISE NOTICE 'Banco demanda_db_dev_repl criado com sucesso.';
    ELSE
        RAISE NOTICE 'Banco demanda_db_dev_repl já existe.';
    END IF;
END
$$;
EOSQL

echo "=== [Master Init] Setup concluído com sucesso! ==="
