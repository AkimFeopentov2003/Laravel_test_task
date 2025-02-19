FROM php:8.4-fpm

# Установка необходимых пакетов
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    curl

# Установка расширений PHP
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo_pgsql pgsql


# Установка Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
