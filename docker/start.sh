#!/bin/bash
# ─────────────────────────────────────────────
# start.sh — Demanda3D Application Entrypoint
# ─────────────────────────────────────────────
set -e

echo "==> Demanda3D: Iniciando container..."

# Aguarda o banco de dados estar pronto (fallback caso depends_on
# com healthcheck não seja suficiente)
echo "==> Aguardando PostgreSQL..."
until php -r "
try {
    new PDO('pgsql:host=\${getenv(\"DB_HOST\")};\${getenv(\"DB_DATABASE\")}', \${getenv(\"DB_USERNAME\")}, \${getenv(\"DB_PASSWORD\")});
    echo 'OK';
} catch (\PDOException \$e) {
    exit(1);
}
" 2>/dev/null; do
    echo "   PostgreSQL ainda não está pronto..."
    sleep 2
done

echo "==> PostgreSQL está pronto."

# Aguarda o Redis
echo "==> Aguardando Redis..."
until php -r "
try {
    \$redis = new Redis();
    \$redis->connect(\${getenv(\"REDIS_HOST\")} ?: '127.0.0.1', (int)(\${getenv(\"REDIS_PORT\")} ?: 6379), 1.0);
    echo 'OK';
} catch (\Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
    echo "   Redis ainda não está pronto..."
    sleep 2
done

echo "==> Redis está pronto."

# Aplica migrations pendentes (DEV apenas — para produção usar job separado)
if [ "${APP_ENV:-production}" = "local" ] || [ "${APP_ENV:-production}" = "dev" ]; then
    echo "==> Executando migrations..."
    php artisan migrate --force --isolated=1 2>/dev/null || echo "   Aviso: migration já aplicada ou ignorada."
fi

# Limpa e regenera cache
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "==> Iniciando PHP-FPM na porta 8080..."
exec php-fpm -F -y /usr/local/etc/php-fpm.d/www.conf 2>&1