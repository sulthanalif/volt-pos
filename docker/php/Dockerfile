FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# Install dependensi sistem, ekstensi PHP, dan Node.js
RUN apk add --no-cache \
    build-base autoconf libzip-dev zip curl nginx \
    nodejs npm \
    libpng-dev libjpeg-turbo-dev freetype-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli zip bcmath pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Atur izin awal
RUN mkdir -p storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

USER www-data
EXPOSE 9000
CMD ["php-fpm"]
