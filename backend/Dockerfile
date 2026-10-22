FROM php:8.2-fpm-alpine

RUN apk update && apk add --no-cache \
    zip \
    unzip \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000
