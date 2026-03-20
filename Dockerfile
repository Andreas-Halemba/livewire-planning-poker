# =============================================================================
# Planning Poker - Multi-Stage Docker Build
# Stage 1: Node.js - Build frontend assets (Vite)
# Stage 2: PHP - Install Composer dependencies
# Stage 3: Nginx - Production web server
# Stage 4: PHP-FPM - Production app server
# =============================================================================

# ---------------------------------------------------------------------------
# Stage 1: Build frontend assets
# ---------------------------------------------------------------------------
    FROM --platform=$BUILDPLATFORM node:24-alpine AS node-builder

    WORKDIR /build

    # Baked into the JS bundle — pass at build time (CI secrets/vars), not from runtime .env.
    ARG VITE_REVERB_APP_KEY=
    ARG VITE_REVERB_HOST=
    ARG VITE_REVERB_PORT=
    ARG VITE_REVERB_SCHEME=

    ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY \
        VITE_REVERB_HOST=$VITE_REVERB_HOST \
        VITE_REVERB_PORT=$VITE_REVERB_PORT \
        VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME

    COPY package.json package-lock.json vite.config.js postcss.config.js tailwind.config.js ./
    COPY resources/ resources/

    RUN npm ci && npm run build

    # ---------------------------------------------------------------------------
    # Stage 2: Install PHP dependencies
    # ---------------------------------------------------------------------------
    FROM --platform=$BUILDPLATFORM composer:2 AS composer-builder

    WORKDIR /build

    COPY composer.json composer.lock ./

    RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader

    # Copy full app and run post-install scripts
    COPY . .
    RUN composer dump-autoload --optimize --no-dev --no-scripts

    # ---------------------------------------------------------------------------
    # Stage 3: Nginx (web server)
    # ---------------------------------------------------------------------------
    FROM nginx:alpine AS nginx

    # Copy only public assets (PHP handled by FPM)
    COPY --from=node-builder /build/public/build /var/www/html/public/build
    COPY public/ /var/www/html/public/

    COPY .docker/nginx.conf /etc/nginx/conf.d/default.conf

    EXPOSE 80

    # ---------------------------------------------------------------------------
    # Stage 4: PHP-FPM (application server)
    # ---------------------------------------------------------------------------
    FROM php:8.4-fpm AS app

    # Install system dependencies
    RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libicu-dev \
        libxml2-dev \
        libxslt1-dev \
        libzip-dev \
        libonig-dev \
        libcurl4-openssl-dev \
        && rm -rf /var/lib/apt/lists/*

    # Install PHP extensions
    RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
        && docker-php-ext-install -j$(nproc) \
            gd \
            intl \
            pdo_mysql \
            mysqli \
            soap \
            xsl \
            zip \
            pcntl \
            sockets \
            exif \
            bcmath \
            opcache

    # Redis extension
    RUN pecl install redis && docker-php-ext-enable redis

    # OPcache config
    RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
        && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
        && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
        && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

    WORKDIR /var/www/html

    # Copy application code
    COPY --from=composer-builder /build/vendor vendor/
    COPY --from=node-builder /build/public/build public/build/
    COPY . .

    # Create storage structure and set permissions
    RUN mkdir -p \
            storage/app/public \
            storage/framework/cache/data \
            storage/framework/sessions \
            storage/framework/views \
            storage/logs \
            bootstrap/cache \
        && chown -R www-data:www-data storage bootstrap/cache \
        && chmod -R 775 storage bootstrap/cache

    COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
    RUN chmod +x /usr/local/bin/entrypoint.sh
    USER www-data

    EXPOSE 9000
    ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
    CMD ["php-fpm"]
