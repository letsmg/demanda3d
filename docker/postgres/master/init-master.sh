#!/bin/sh
# =============================================
# Demanda3D - PostgreSQL Master Init
# Runs during first boot (after initdb, before final start)
# =============================================
set -e

echo "=== [Master Init] Overriding pg_hba.conf for replication support ==="

cat > "$PGDATA/pg_hba.conf" << 'EOF'
# TYPE  DATABASE        USER            ADDRESS                 METHOD
local   all             all                                     md5
host    all             all             127.0.0.1/32            md5
host    all             all             ::1/128                 md5
host    all             all             0.0.0.0/0               md5
local   replication     all                                     trust
host    replication     all             0.0.0.0/0               md5
host    replication     all             ::0/0                   md5
EOF

echo "=== [Master Init] pg_hba.conf updated ==="