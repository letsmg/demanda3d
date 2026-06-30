#!/bin/sh
set -e

# O PostgreSQL Alpine 18.4 com POSTGRES_USER customizado (ex: dev_demanda_user)
# NÃO cria o role "postgres". O usuário customizado é o superuser.
export PGUSER="${POSTGRES_USER:?POSTGRES_USER must be set}"
export PGPASSWORD="${POSTGRES_PASSWORD:?POSTGRES_PASSWORD must be set}"

echo "=== [Master Init] 1/3: Configurando pg_hba.conf ==="
cat > "$PGDATA/pg_hba.conf" << 'EOF'
local   all             all                                     md5
host    all             all             127.0.0.1/32            md5
host    all             all             ::1/128                 md5
host    all             all             172.16.0.0/12           md5
local   replication     all                                     md5
host    replication     all             172.16.0.0/12           md5
EOF

echo "=== [Master Init] 2/3: Aplicando configurações do sistema ==="
psql -d postgres << EOSQL
-- Configurações do Sistema
ALTER SYSTEM SET listen_addresses = '*';
ALTER SYSTEM SET wal_level = 'replica';
ALTER SYSTEM SET max_wal_senders = 10;
ALTER SYSTEM SET wal_keep_size = '128MB';
ALTER SYSTEM SET max_replication_slots = 5;
ALTER SYSTEM SET hot_standby = on;
ALTER SYSTEM SET wal_log_hints = on;
ALTER SYSTEM SET synchronous_commit = 'on';
ALTER SYSTEM SET shared_buffers = '256MB';
ALTER SYSTEM SET effective_cache_size = '768MB';
ALTER SYSTEM SET maintenance_work_mem = '64MB';
ALTER SYSTEM SET work_mem = '4MB';
ALTER SYSTEM SET random_page_cost = 1.1;
ALTER SYSTEM SET timezone = 'America/Sao_Paulo';
ALTER SYSTEM SET log_timezone = 'America/Sao_Paulo';
ALTER SYSTEM SET datestyle = 'iso, dmy';
SELECT pg_reload_conf();

-- Criação segura do usuário de replicação
-- Evita erro se o usuário já existir e define a permissão estrita de REPLICATION
DO \$\$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = '${REPL_USER:-dev_demanda_user}') THEN
        CREATE ROLE ${REPL_USER:-dev_demanda_user} WITH REPLICATION LOGIN PASSWORD '${REPL_PASSWORD:?REPL_PASSWORD must be set}';
    END IF;
END
\$\$;
EOSQL

echo "=== [Master Init] 3/3: Criando slot de replicação ==="
psql -d postgres << EOSQL
SELECT pg_create_physical_replication_slot('${REPL_SLOT:-dev_demanda_replica_slot}', true)
WHERE NOT EXISTS (
    SELECT 1 FROM pg_replication_slots WHERE slot_name = '${REPL_SLOT:-dev_demanda_replica_slot}'
);
EOSQL

echo "=== [Master Init] Setup concluído com sucesso! ==="