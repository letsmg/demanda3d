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
psql -v ON_ERROR_STOP=1 -U "${POSTGRES_USER:-postgres}" -d "${POSTGRES_DB:-postgres}" <<'EOSQL'
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

# Adiciona entrada de replicação no pg_hba.conf para permitir conexões da réplica
# O arquivo pg_hba.conf fica no diretório de dados do PostgreSQL ($PGDATA)
PG_HBA="${PGDATA:-/var/lib/postgresql/data}/pg_hba.conf"
if ! grep -q "replication" "$PG_HBA" 2>/dev/null; then
    echo "host replication all samenet trust" >> "$PG_HBA"
    echo "host replication all 0.0.0.0/0 md5" >> "$PG_HBA"
    pg_ctl reload -D "${PGDATA:-/var/lib/postgresql/data}" 2>/dev/null || true
    echo "=== [Master Init] pg_hba.conf atualizado para replicação ==="
fi

echo "=== [Master Init] Setup concluído com sucesso! ==="
