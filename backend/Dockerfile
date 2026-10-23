FROM composer:2.8.12 as composer-stage
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader

FROM php:8.2-fpm-alpine as production-stage

RUN apk update && apk add --no-cache \
    zip \
    unzip \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /var/www

COPY . .

COPY --from=composer-stage /app/vendor ./vendor

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
