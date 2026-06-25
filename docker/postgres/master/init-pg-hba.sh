#!/bin/sh
# =============================================
# Override pg_hba.conf for replication support
# Demanda3D - PostgreSQL Master
# =============================================
cat > "${PGDATA}/pg_hba.conf" << 'EOF'
# TYPE  DATABASE    USER            ADDRESS                 METHOD
local   replication all                                     trust
host    replication all             all                     md5
local   all         all                                     md5
host    all         all             all                     md5
EOF

echo "[pg_hba] Replication auth configured."