#!/bin/sh
set -e

# ==============================================================================
# entrypoint-replica.sh — Entrypoint customizado para PostgreSQL Réplica
#
# ARQUITETURA (leia antes de alterar):
# Este script substitui o padrão de usar docker-entrypoint-initdb.d para
# inicializar a réplica. O motivo é crítico: scripts em initdb.d rodam
# ENQUANTO um Postgres temporário está ativo usando $PGDATA. Apagar e
# reescrever $PGDATA nesse momento corrompe o cluster (pg_filenode.map
# ausente, crash-loop imediato).
#
# Fluxo correto implementado aqui:
# 1. Se $PGDATA está vazio → pg_basebackup ANTES de qualquer Postgres subir.
# 2. Verifica que o basebackup completou (PG_VERSION + pg_filenode.map).
# 3. Entrega controle para docker-entrypoint.sh oficial, que detecta
#    PG_VERSION presente e PULA o initdb, subindo o Postgres direto.
#
# Em reinícios normais (volume já populado), o basebackup é pulado — a
# réplica sobe com os dados que já existem no volume, como esperado.
#
# Pré-requisitos (via .env.docker):
#   MASTER_HOST, MASTER_PORT   — endereço do master
#   REPL_USER, REPL_PASSWORD   — usuário com permissão REPLICATION no master
#   REPL_SLOT                  — slot físico já criado no master (00-init-master.sh)
# ==============================================================================

MASTER_HOST="${MASTER_HOST:?Erro: MASTER_HOST não definida}"
MASTER_PORT="${MASTER_PORT:-5432}"
REPL_USER="${REPL_USER:?Erro: REPL_USER não definida}"
REPL_PASSWORD="${REPL_PASSWORD:?Erro: REPL_PASSWORD não definida}"
SLOT_NAME="${REPL_SLOT:?Erro: REPL_SLOT não definida}"

if [ ! -s "$PGDATA/PG_VERSION" ]; then
    echo "=== [Replica Init] PGDATA vazio — iniciando pg_basebackup... ==="

    # Aguarda o master aceitar conexões de replicação especificamente
    # (pg_isready confirma conexões normais, mas o slot de replicação
    # pode ainda não estar pronto — aguardamos com retry explícito).
    echo "=== [Replica Init] Aguardando master ficar pronto... ==="
    RETRIES=30
    until PGPASSWORD="$REPL_PASSWORD" pg_isready \
            -h "$MASTER_HOST" \
            -p "$MASTER_PORT" \
            -U "$REPL_USER" > /dev/null 2>&1; do
        RETRIES=$((RETRIES - 1))
        if [ "$RETRIES" -le 0 ]; then
            echo "❌ ERRO: Master não respondeu após 90 segundos. Abortando."
            exit 1
        fi
        echo "   Master ainda não está pronto... (tentativas restantes: $RETRIES)"
        sleep 3
    done

    echo "=== [Replica Init] Master pronto. Executando pg_basebackup... ==="

    # Limpa qualquer resíduo parcial de tentativas anteriores
    rm -rf "$PGDATA"/*

    # Flags:
    #   -Xs  : inclui WAL via streaming (mais confiável que fetch)
    #   -P   : progresso visível no log
    #   -R   : gera standby.signal + postgresql.auto.conf com primary_conninfo
    #   -S   : usa o slot de replicação físico já criado no master
    #   -v   : verboso (facilita diagnóstico de falha)
    PGPASSWORD="$REPL_PASSWORD" pg_basebackup \
        -h "$MASTER_HOST" \
        -p "$MASTER_PORT" \
        -U "$REPL_USER" \
        -D "$PGDATA" \
        -Xs \
        -P \
        -R \
        -S "$SLOT_NAME" \
        -v

    # ===========================================================================
    # Verificação explícita de integridade pós-basebackup.
    # Sem isso, o container pode subir "healthy" (pg_isready passa) mesmo com
    # um cluster incompleto — foi exatamente o que causou o comportamento
    # silencioso em produção e o crash-loop em hom.
    # ===========================================================================
    if [ ! -s "$PGDATA/PG_VERSION" ]; then
        echo "❌ ERRO: pg_basebackup falhou — PG_VERSION ausente em $PGDATA."
        echo "   Verifique se o slot '$SLOT_NAME' existe no master e se"
        echo "   '$REPL_USER' tem permissão REPLICATION."
        exit 1
    fi

    if [ ! -f "$PGDATA/global/pg_filenode.map" ]; then
        echo "❌ ERRO: pg_basebackup incompleto — pg_filenode.map ausente."
        echo "   O backup foi interrompido antes de completar a cópia global."
        exit 1
    fi

    chown -R postgres:postgres "$PGDATA"
    chmod 700 "$PGDATA"

    echo "=== [Replica Init] Backup concluído e verificado com sucesso. ==="
else
    echo "=== [Replica Init] PGDATA já populado (PG_VERSION presente). ==="
    echo "=== [Replica Init] Pulando pg_basebackup — subindo réplica normalmente. ==="
fi

# Entrega o controle para o entrypoint oficial da imagem postgres:alpine.
# Como PG_VERSION já existe em $PGDATA, ele detecta um cluster existente,
# pula o initdb completamente, e sobe o Postgres em modo standby
# (standby.signal gerado pelo -R do pg_basebackup já está presente).
exec docker-entrypoint.sh postgres