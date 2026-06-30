#!/bin/sh
set -e

# Configurações
MASTER_HOST="${MASTER_HOST:-demanda-psql-dev}"
MASTER_PORT=5432
REPL_USER="${REPL_USER:-demanda_user}"
export PGPASSWORD="${REPL_PASSWORD:?Erro: REPL_PASSWORD não definida}"
SLOT_NAME="${REPL_SLOT:-demanda_replica_slot_dev}"

echo "=== [Replica Init] Aguardando Master..."
until pg_isready -h "$MASTER_HOST" -p "$MASTER_PORT" -U "$REPL_USER"; do
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
    -C \
    -S "$SLOT_NAME" \
    -v

# Ajuste de dono e permissão - CRÍTICO PARA O DOCKER
chown -R postgres:postgres "$PGDATA"
chmod 700 "$PGDATA"

echo "=== [Replica Init] Backup concluído e permissões ajustadas."