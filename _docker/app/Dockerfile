FROM php:8.2-fpm

# Установка зависимостей и расширений PHP
RUN apt-get update -o Acquire::http::Pipeline-Depth=200 && apt-get install -y --no-install-recommends \
    apt-utils \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git && \
    docker-php-ext-install \
        pdo_mysql \
        bcmath \
        gd \
        zip \
        exif \
        mysqli && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
    
# Копирование кастомного php.ini
COPY ./_docker/app/php.ini /usr/local/etc/php/conf.d/php.ini

# Установка рабочего каталога
WORKDIR /var/www


