FROM php:8.4-fpm-alpine

# Instalar dependencias del sistema y extensiones de PHP
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libzip-dev

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Configurar límites de subida de PHP
RUN { \
    echo 'upload_max_filesize = 100M'; \
    echo 'post_max_size = 108M'; \
    echo 'memory_limit = 256M'; \
} > /usr/local/etc/php/conf.d/uploads.ini

# Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Usuario para evitar problemas de permisos
RUN addgroup -g 1000 laravel && adduser -G laravel -u 1000 -D laravel
USER laravel
