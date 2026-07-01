#!/bin/sh
# Script de inicialização mínima para o PostgreSQL Master
# A autenticação trust para conexões locais já é configurada pelo
# POSTGRES_HOST_AUTH_METHOD=trust no docker-compose.yml.
# Este script apenas ajusta parâmetros de replicação essenciais.

set -e

echo "=== [Master Init] Aplicando parâmetros de replicação ==="

# Aplica apenas as configurações essenciais de replicação.
# Parâmetros de performance (shared_buffers, etc.) são deixados com defaults
# do PostgreSQL para evitar incompatibilidades com a versão Alpine.
psql -v ON_ERROR_STOP=1 -U postgres -d postgres <<'EOSQL'
ALTER SYSTEM SET listen_addresses = '*';
ALTER SYSTEM SET wal_level = 'replica';
ALTER SYSTEM SET max_wal_senders = 10;
ALTER SYSTEM SET wal_keep_size = '128MB';
ALTER SYSTEM SET max_replication_slots = 5;
ALTER SYSTEM SET hot_standby = on;
ALTER SYSTEM SET wal_log_hints = on;
ALTER SYSTEM SET synchronous_commit = 'on';
SELECT pg_reload_conf();
EOSQL

echo "=== [Master Init] Setup concluído com sucesso! ==="