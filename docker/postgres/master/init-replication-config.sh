#!/bin/sh
# =============================================
# Demanda3D - PostgreSQL Master Replication Config
# Executa após o postgres iniciar (dentro do entrypoint)
# Aplica parâmetros via ALTER SYSTEM
# =============================================
set -e

echo "=== [Master Config] Applying replication settings via ALTER SYSTEM ==="

export PGPASSWORD="${POSTGRES_PASSWORD:?POSTGRES_PASSWORD must be set in .env.docker}"

psql -U demanda_user -d demanda_db << 'EOSQL'
-- Replication fundamentals
ALTER SYSTEM SET listen_addresses = '*';
ALTER SYSTEM SET wal_level = 'replica';
ALTER SYSTEM SET max_wal_senders = 5;
ALTER SYSTEM SET wal_keep_size = 64;
ALTER SYSTEM SET max_replication_slots = 5;
ALTER SYSTEM SET hot_standby = on;
ALTER SYSTEM SET wal_log_hints = on;

-- Sync replication (relaxed to 'on' for compatibility; can be tightened later)
ALTER SYSTEM SET synchronous_commit = 'on';
-- ALTER SYSTEM SET synchronous_standby_names = 'demanda3d_replica';

-- Performance
ALTER SYSTEM SET shared_buffers = '256MB';
ALTER SYSTEM SET effective_cache_size = '768MB';
ALTER SYSTEM SET maintenance_work_mem = '64MB';
ALTER SYSTEM SET work_mem = '4MB';

-- Timezone
ALTER SYSTEM SET timezone = 'America/Sao_Paulo';
ALTER SYSTEM SET log_timezone = 'America/Sao_Paulo';
ALTER SYSTEM SET datestyle = 'iso, dmy';

-- Reload configuration
SELECT pg_reload_conf();
EOSQL

echo "=== [Master Config] Replication settings applied ==="