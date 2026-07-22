FROM php:8.2-fpm

# Dépendances système + nginx + supervisor
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    nginx \
    supervisor \
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

# Extension MongoDB (version compatible avec composer.lock)
RUN pecl install mongodb-1.21.2 \
    && docker-php-ext-enable mongodb

# Variables d'environnement
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Configuration PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Répertoire de travail
WORKDIR /var/www/html

# Copie du projet
COPY . .

# Installation des dépendances
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Compilation des assets
RUN php bin/console asset-map:compile || true

# Préparation des dossiers Symfony
RUN mkdir -p var/cache var/log var/sessions \
    && chown -R www-data:www-data var

# Configuration nginx
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Configuration Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Entrypoint
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["/usr/bin/supervisord","-c","/etc/supervisor/conf.d/supervisord.conf"]