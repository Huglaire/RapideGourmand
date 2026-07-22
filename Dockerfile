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

# MongoDB (version compatible avec le composer.lock)
RUN pecl install mongodb-1.21.2 \
    && docker-php-ext-enable mongodb

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# PHP personnalisé
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Entrypoint
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["php-fpm"]