#!/bin/bash
# Script de inicialização — Configura o PostgreSQL master para replicação lógica

set -e

# Habilita wal_level=logical (necessário para logical replication)
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    ALTER SYSTEM SET wal_level = 'logical';
    ALTER SYSTEM SET max_wal_senders = 10;
    ALTER SYSTEM SET max_replication_slots = 10;
    SELECT pg_reload_conf();
EOSQL

echo "✅ Master configurado para replicação lógica (wal_level=logical)"