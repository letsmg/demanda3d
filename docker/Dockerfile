# ─────────────────────────────────────────────
# Dockerfile — Demanda3D Application
# Laravel 11 + PHP 8.3 FPM (Alpine)
# ─────────────────────────────────────────────
FROM php:8.5-fpm-alpine

LABEL maintainer="luiztsilva@protonmail"
LABEL description="Demanda3D — Laravel Application Container"

# ── System dependencies ──────────────────────
RUN apk add --no-cache \
    bash \
    curl \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pgsql \
    zip \
    opcache
    

# ── zip extension (via libzip) ────────────────
RUN docker-php-ext-configure zip \
    && docker-php-ext-install -j$(nproc) zip

# ── Redis extension ──────────────────────────
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

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