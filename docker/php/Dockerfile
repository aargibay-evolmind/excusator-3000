FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl

# Ajuste crítico de permisos para coincidir con Host (UID 1000)
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Configurar home directory para www-data
RUN usermod -d /home/dev www-data

# Configuración de directorios de trabajo
RUN mkdir -p /home/dev/code /home/dev/.composer
RUN chown -R www-data:www-data /home/dev/

# Configurar variables de entorno para Composer
ENV COMPOSER_HOME=/home/dev/.composer
ENV COMPOSER_CACHE_DIR=/home/dev/.composer/cache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuración final
USER 1000
WORKDIR /home/dev/code
