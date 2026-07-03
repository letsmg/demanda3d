# ── Base Image ────────────────────────────────
FROM php:8.5-fpm-bookworm

LABEL maintainer="luiztsilva@protonmail"

# ── System dependencies ──────────────────────
# O Debian gerencia o DNS automaticamente, sem erros de "Read-only"
RUN apt-get update && apt-get install -y --no-install-recommends \
    bash \
    curl \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pgsql \
    zip \
    opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ── Redis extension ──────────────────────────
# No Debian, usamos pecl diretamente
RUN pecl install redis \
    && docker-php-ext-enable redis

# ── Composer ─────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── App directory ────────────────────────────
WORKDIR /var/www/demanda3d

# ── Copy app code ────────────────────────────
COPY . .

# ── Permissions ──────────────────────────────
RUN chown -R www-data:www-data /var/www/demanda3d \
    && chmod -R 775 /var/www/demanda3d/storage \
    && chmod -R 775 /var/www/demanda3d/bootstrap/cache

# ── Install PHP dependencies ─────────────────
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# ── Start script ─────────────────────────────
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/start.sh"]