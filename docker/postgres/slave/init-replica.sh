#!/bin/sh
# =============================================
# Demanda3D - PostgreSQL Replica Init
# Executa na primeira inicialização do container replica.
# Remove o data dir recém-criado pelo initdb e faz
# pg_basebackup do master, configurando o servidor
# como standby (hot standby).
# =============================================
set -e

MASTER_HOST="${MASTER_HOST:-postgres-demanda3d}"
MASTER_PORT="${MASTER_PORT:-5432}"
REPL_USER="${REPL_USER:-demanda_user}"
export PGPASSWORD="${REPL_PASSWORD:?REPL_PASSWORD must be set in .env.docker}"
SLOT_NAME="${REPL_SLOT:-demanda3d_replica_slot}"
APP_NAME="${REPL_APP_NAME:-demanda3d_replica}"

echo "=== [Replica Init] Waiting for master to be ready (host=$MASTER_HOST:$MASTER_PORT)..."
until pg_isready -h "$MASTER_HOST" -p "$MASTER_PORT" -U "$REPL_USER" -d demanda_db; do
    echo "    Master not ready yet, retrying in 3s..."
    sleep 3
done
echo "=== [Replica Init] Master is reachable. Starting base backup..."

# Remove the freshly created initdb data
echo "=== [Replica Init] Removing fresh init data at $PGDATA..."
rm -rf "${PGDATA:?}"/*

# Perform the base backup using the replication slot
echo "=== [Replica Init] Running pg_basebackup..."
pg_basebackup \
    -h "$MASTER_HOST" \
    -p "$MASTER_PORT" \
    -U "$REPL_USER" \
    -D "$PGDATA" \
    -Fp \
    -Xs \
    -P \
    -R \
    -C \
    -S "$SLOT_NAME" \
    -v

# Ensure standby mode is activated
touch "$PGDATA/standby.signal"

# Set permissions
chmod 700 "$PGDATA"

echo "=== [Replica Init] Replica setup complete. Will start as hot standby."