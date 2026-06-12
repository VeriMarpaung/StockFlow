# ── Stage 1: Dependencies ──────────────────────────────────
FROM composer:2.7 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize

# ── Stage 2: Production Image ──────────────────────────────
FROM php:8.3-fpm-alpine

# Install build deps + runtime deps sekaligus,
# lalu PECL di sini sebelum build deps di-uninstall
RUN apk add --no-cache \
        nginx \
        postgresql-dev \
        supervisor \
        autoconf \
        g++ \
        make \
        ca-certificates \
    && docker-php-ext-install pdo pdo_pgsql bcmath opcache \
    && pecl install redis \
    && docker-php-ext-enable redis opcache \
    # Hapus build deps setelah PECL selesai
    && apk del autoconf g++ make \
    && rm -rf /tmp/pear

WORKDIR /var/www/html

# Copy app from vendor stage
COPY --from=vendor /app /var/www/html
COPY --from=vendor /app/vendor /var/www/html/vendor

# Copy nginx + supervisor config
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache


RUN mkdir -p /var/log/supervisor
EXPOSE 8000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]