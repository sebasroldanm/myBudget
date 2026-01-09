FROM php:8.4-fpm-alpine

# --------------------------------------------------
# System dependencies + Node.js
# --------------------------------------------------
RUN apk add --no-cache \
    bash \
    curl \
    zip \
    unzip \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    postgresql-dev \
    icu-dev \
    linux-headers \
    fcgi \
    nodejs \
    npm

# --------------------------------------------------
# PHP extensions
# --------------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    opcache

# --------------------------------------------------
# Redis extension
# --------------------------------------------------
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del pcre-dev $PHPIZE_DEPS

# --------------------------------------------------
# Xdebug
# --------------------------------------------------
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug-3.4.0 \
    && docker-php-ext-enable xdebug \
    && apk del $PHPIZE_DEPS

# --------------------------------------------------
# Composer
# --------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --------------------------------------------------
# PHP-FPM healthcheck
# --------------------------------------------------
RUN echo '#!/bin/sh' > /usr/local/bin/php-fpm-healthcheck \
    && echo 'SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000' >> /usr/local/bin/php-fpm-healthcheck \
    && chmod +x /usr/local/bin/php-fpm-healthcheck

# --------------------------------------------------
# User www-data (UID 1000)
# --------------------------------------------------
RUN deluser www-data 2>/dev/null || true \
    && delgroup www-data 2>/dev/null || true \
    && addgroup -g 1000 www-data \
    && adduser -u 1000 -D -S -G www-data www-data

# --------------------------------------------------
# npm cache y HOME (CRÍTICO)
# --------------------------------------------------
ENV HOME=/home/www-data
ENV NPM_CONFIG_CACHE=/home/www-data/.npm

RUN mkdir -p /home/www-data/.npm \
    && chown -R www-data:www-data /home/www-data

# --------------------------------------------------
# WORKDIR
# --------------------------------------------------
WORKDIR /var/www/html

# --------------------------------------------------
# PHP config (copiar al final para aprovechar cache)
# --------------------------------------------------
COPY ./php.ini /usr/local/etc/php/conf.d/custom.ini
COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY ./www.conf /usr/local/etc/php-fpm.d/www.conf

USER www-data

EXPOSE 9000
CMD ["php-fpm"]