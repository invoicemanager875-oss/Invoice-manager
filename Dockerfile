# =========================================================
# Stage 1 - Build asset frontend (Vite + Tailwind)
# =========================================================
FROM node:20-alpine AS assets

WORKDIR /app

# Install dependency dulu supaya layer ini ke-cache
COPY package.json package-lock.json ./
RUN npm ci

# Copy seluruh source: Tailwind perlu scan blade/js untuk generate class.
# Aman untuk Tailwind v3 (pakai tailwind.config.js) maupun v4 (config di app.css).
COPY . .

RUN npm run build
# Hasil: /app/public/build (manifest.json + assets)


# =========================================================
# Stage 2 - Composer dependencies
# =========================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
      --no-dev \
      --no-scripts \
      --no-autoloader \
      --prefer-dist \
      --no-interaction


# =========================================================
# Stage 3 - Runtime (nginx + php-fpm dalam satu container)
# =========================================================
FROM serversideup/php:8.3-fpm-nginx AS runtime

# Image ini listen di port 8080 sebagai non-root user.
# Northflank auto-detect port dari baris EXPOSE ini.
EXPOSE 8080

# Extension tambahan. Kebanyakan sudah bawaan image.
# Kalau build gagal di baris ini, hapus saja blok USER root ... USER www-data.
USER root
RUN install-php-extensions pdo_pgsql pdo_mysql redis intl bcmath gd
USER www-data

WORKDIR /var/www/html

# Copy source aplikasi
COPY --chown=www-data:www-data . .

# Copy hasil build dari stage sebelumnya
COPY --from=vendor  --chown=www-data:www-data /app/vendor       ./vendor
COPY --from=assets  --chown=www-data:www-data /app/public/build ./public/build

# Generate autoloader final (butuh source lengkap)
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

# PENTING: JANGAN jalankan `php artisan config:cache` di sini.
# Env var Northflank baru di-inject saat runtime, bukan saat build.
# Caching ditangani entrypoint lewat AUTORUN_* di bawah.

ENV PHP_OPCACHE_ENABLE=1

ENV AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_STORAGE_LINK=true \
    AUTORUN_LARAVEL_CONFIG_CACHE=true \
    AUTORUN_LARAVEL_ROUTE_CACHE=true \
    AUTORUN_LARAVEL_VIEW_CACHE=true \
    AUTORUN_LARAVEL_MIGRATION=false