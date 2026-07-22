FROM php:8.2-fpm

# Dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    nano \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libxml2-dev \
    libxslt1-dev \
    libonig-dev \
    libssl-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Configuration GD
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

# Extensions PHP
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    intl \
    zip \
    gd \
    xsl \
    opcache \
    exif

# Extension MongoDB
RUN pecl install mongodb-1.21.2 \
    && docker-php-ext-enable mongodb

# Autoriser les plugins Composer pendant le build
ENV COMPOSER_ALLOW_SUPERUSER=1

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Configuration PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Entrypoint
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Dossier de travail
WORKDIR /var/www/html

# Copie de l'application
COPY . .

# Installation des dépendances PHP
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Compilation des assets Symfony (AssetMapper)
RUN php bin/console asset-map:compile || true

# Préparation des dossiers Symfony
RUN mkdir -p var/cache var/log var/sessions \
    && chown -R www-data:www-data var

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["php-fpm"]