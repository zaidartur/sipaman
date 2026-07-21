# syntax=docker/dockerfile:1
#
# Dockerfile.prod — Image gabungan (PHP-FPM + Nginx + Supervisor) untuk production
# Menggantikan pasangan Dockerfile.prod + Dockerfile_nginx.prod sebelumnya.
#
# Arsitektur:
#  - Stage 1  : build asset frontend (Vite/Node)
#  - Stage app: PHP 8.4-FPM + Nginx + Supervisor dalam satu container
#  - Nginx mem-forward request PHP ke php-fpm via 127.0.0.1:9000 (loopback only)
#  - Proses yang menangani request (nginx, php-fpm) berjalan non-root
#    (www-data); supervisord sebagai orkestrator tetap root — lihat
#    catatan keamanan di dekat ENTRYPOINT/CMD di bawah

# =========================================================
# STAGE 1: Build Frontend Assets (Vite/Node)
# =========================================================
FROM node:22-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build


# =========================================================
# STAGE 2: Runtime Image — PHP-FPM + Nginx + Supervisor
# =========================================================
FROM php:8.4-fpm-alpine AS app

WORKDIR /var/www

# --- 1) System deps & PHP extensions -----------------------------------
# Paket *-dev / toolchain kompilasi dipisah via virtual group ".build-deps"
# lalu dihapus setelah selesai kompilasi -> mengurangi ukuran image &
# attack surface (tidak ada compiler/header tersisa di image production).
RUN set -eux; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
        libxml2-dev \
        curl-dev; \
    apk add --no-cache \
        curl \
        supervisor \
        nginx \
        libpng \
        libjpeg-turbo \
        freetype \
        libzip \
        oniguruma \
        libxml2 \
        zip \
        unzip; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
        pdo_mysql mbstring zip exif pcntl bcmath gd opcache \
        curl fileinfo simplexml; \
    apk del .build-deps; \
    rm -rf /var/cache/apk/*
# curl, fileinfo, simplexml ditambahkan karena dibutuhkan AWS SDK/Flysystem
# S3 driver (dipakai Laravel untuk konek ke MinIO via disk "s3").

# --- 2) Samakan UID/GID www-data dengan user host (opsional tapi disarankan)
# Berguna jika storage/logs di-bind-mount ke host (lihat permintaan log
# laravel.log bisa dibaca dari host): dengan UID/GID yang sama, file yang
# ditulis container tetap bisa dibaca/ditulis oleh user host tanpa perlu
# root/sudo. Default 1000:1000 (user pertama di kebanyakan distro Linux).
# Override saat build: --build-arg APP_UID=$(id -u) --build-arg APP_GID=$(id -g)
ARG APP_UID=1000
ARG APP_GID=1000
RUN deluser www-data 2>/dev/null || true; \
    delgroup www-data 2>/dev/null || true; \
    addgroup -g "${APP_GID}" www-data; \
    adduser -D -H -u "${APP_UID}" -G www-data -s /sbin/nologin www-data

# --- 3) Composer (versi di-pin, bukan "latest") -------------------------
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# --- 4) Source code aplikasi ---------------------------------------------
# --chown langsung set ownership ke www-data saat COPY (tidak perlu layer
# RUN chown -R terpisah yang memperbesar image).
COPY --chown=www-data:www-data . /var/www

# Hasil build frontend dari Stage 1
COPY --from=frontend-builder --chown=www-data:www-data /app/public/build /var/www/public/build

# --- 5) Install dependency PHP untuk production --------------------------
# COMPOSER_ALLOW_SUPERUSER hanya relevan saat BUILD TIME (proses build
# berjalan sebagai root secara default di Docker). Ini tidak berdampak
# pada user runtime container (lihat ENTRYPOINT di bawah).
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        --no-progress \
    && composer clear-cache

# --- 6) Konfigurasi PHP production hardening ------------------------------
COPY docker-config/php/opcache.ini  /usr/local/etc/php/conf.d/opcache.ini
COPY docker-config/php/uploads.ini  /usr/local/etc/php/conf.d/uploads.ini
COPY docker-config/php/security.ini /usr/local/etc/php/conf.d/security.ini

# php-fpm hanya boleh diakses dari dalam container sendiri (loopback),
# tidak dari network docker lain / luar container.
RUN sed -i "s/^listen = .*/listen = 127.0.0.1:9000/" /usr/local/etc/php-fpm.d/www.conf

# --- 7) Konfigurasi Nginx --------------------------------------------------
RUN rm -f /etc/nginx/http.d/default.conf /etc/nginx/conf.d/default.conf 2>/dev/null || true
COPY docker-config/nginx/app.conf /etc/nginx/http.d/app.conf

# --- 8) Konfigurasi Supervisor (menjalankan php-fpm + nginx bersamaan) ----
COPY docker-config/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

# --- 9) Siapkan direktori runtime & permission ----------------------------
RUN set -eux; \
    mkdir -p /var/www/storage /var/www/bootstrap/cache \
             /var/lib/nginx/tmp /var/log/nginx /run/nginx; \
    chown -R www-data:www-data \
        /var/www/storage \
        /var/www/bootstrap/cache \
        /var/lib/nginx \
        /var/log/nginx \
        /run/nginx; \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# --- 10) Entrypoint ----------------------------------------------------------
COPY docker-config/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Nginx listen di port non-privileged (8080) -> tidak butuh root untuk bind.
# Publish/mapping ke 80/443 dilakukan di reverse proxy / load balancer di
# depan container (mis. Traefik, ALB, Cloudflare Tunnel, dst).
EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8080/healthz || exit 1

# CATATAN KEAMANAN:
# Supervisord (PID 1) SENGAJA tetap berjalan sebagai root — bukan diabaikan,
# tapi pilihan sadar karena men-drop privilege supervisord itu sendiri (mis.
# via su-exec sebelum exec) menyebabkan gagal membuka ulang /dev/stdout milik
# proses root sebelumnya (EACCES). Supervisord sendiri tidak pernah membuka
# port atau memproses request jaringan; ia murni orkestrator proses.
# Privilege drop yang sesungguhnya dilakukan PER CHILD PROCESS oleh
# supervisord sendiri lewat directive `user=www-data` di masing-masing
# [program:x] pada supervisord.conf — sehingga nginx & php-fpm, yang benar-
# benar menangani request dari luar, tetap berjalan sebagai www-data
# (non-root). Ini pola standar & umum dipakai untuk kombinasi
# Supervisor + Docker (root-managed init, non-root worker).
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
