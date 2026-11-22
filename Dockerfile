FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        libwebp-dev libjpeg-dev libpng-dev libfreetype6-dev libonig-dev libcurl4-openssl-dev curl $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-webp --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" gd pdo_mysql mbstring curl \
    && pecl install xdebug && docker-php-ext-enable xdebug \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini
COPY docker/apache.conf /etc/apache2/conf-available/app.conf
RUN a2enconf app
