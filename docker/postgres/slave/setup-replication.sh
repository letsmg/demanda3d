#!/bin/sh
# =============================================
# PostgreSQL Replica Setup
# Demanda3D - Streaming Replication
# =============================================
# Este script executa como entrypoint-initdb.d (após o initdb
# mas antes do servidor iniciar). Ele limpa o data dir recem-criado,
# faz pg_basebackup do master, e configura standby.signal.
# =============================================
set -e

MASTER_HOST="${MASTER_HOST:-postgres-demanda3d}"
MASTER_PORT=5432
REPL_USER="${REPL_USER:-demanda_user}"
export PGPASSWORD="${REPL_PASSWORD:?REPL_PASSWORD must be set in .env.docker}"
SLOT_NAME="${REPL_SLOT:-demanda3d_replica_slot}"

echo "=== [Replica Init] Waiting for master to be ready..."
until pg_isready -h "$MASTER_HOST" -p "$MASTER_PORT" -U "$REPL_USER" -d demanda_db; do
    echo "    Master not ready yet, retrying in 3s..."
    sleep 3
done
echo "=== [Replica Init] Master is ready."

# Remove the fresh initdb data and replace with base backup
echo "=== [Replica Init] Removing fresh init data..."
rm -rf "${PGDATA:?}"/*

echo "=== [Replica Init] Running pg_basebackup from master..."
pg_basebackup \
    -h "$MASTER_HOST" \
    -p "$MASTER_PORT" \
    -U "$REPL_USER" \
    -D "$PGDATA" \
    -Fp \
    -Xs \
    -P \
    -R \
    -v

echo "=== [Replica Init] Creating standby.signal..."
touch "$PGDATA/standby.signal"

# Write replication connection info (pg_basebackup -R may have created it)
# but ensure it's correct with our settings
if [ ! -f "$PGDATA/postgresql.auto.conf" ]; then
    cat > "$PGDATA/postgresql.auto.conf" << EOFREPL
primary_conninfo = 'host=${MASTER_HOST} port=${MASTER_PORT} user=${REPL_USER} password=${PGPASSWORD} application_name=demanda3d_replica'
primary_slot_name = '${SLOT_NAME}'
EOFREPL
fi

chmod 700 "$PGDATA"
echo "=== [Replica Init] Setup complete. Server will start as standby."