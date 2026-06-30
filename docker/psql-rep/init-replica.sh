#!/bin/sh
set -e

# Configurações
MASTER_HOST="${MASTER_HOST:?Erro: MASTER_HOST não definida}"
MASTER_PORT="${MASTER_PORT:-5432}"
REPL_USER="${REPL_USER:?Erro: REPL_USER não definida}"
export PGPASSWORD="${REPL_PASSWORD:?Erro: REPL_PASSWORD não definida}"
SLOT_NAME="${REPL_SLOT:?Erro: REPL_SLOT não definido}"
echo "=== [Replica Init] Master: $MASTER_HOST:$MASTER_PORT | User: $REPL_USER | Slot: $SLOT_NAME"
echo "=== [Replica Init] Aguardando Master..."
until pg_isready -h "$MASTER_HOST" -p "$MASTER_PORT" -U "$REPL_USER" -d postgres; do
    sleep 3
done

echo "=== [Replica Init] Limpando diretório e iniciando backup..."
rm -rf "$PGDATA"/*

# O pg_basebackup com -R e -C automatiza quase tudo
pg_basebackup \
    -h "$MASTER_HOST" \
    -p "$MASTER_PORT" \
    -U "$REPL_USER" \
    -D "$PGDATA" \
    -Fp \
    -Xs \
    -P \
    -R \
    -S "$SLOT_NAME" \
    -v

# Ajuste de dono e permissão - CRÍTICO PARA O DOCKER
chown -R postgres:postgres "$PGDATA"
chmod 700 "$PGDATA"

echo "=== [Replica Init] Backup concluído e permissões ajustadas."