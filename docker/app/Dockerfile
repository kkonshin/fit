FROM php:8.5-fpm

ARG PUID
ARG PGID
ARG PHPIZE_DEPS

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    apt-utils \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    zip unzip \
    git \
    $PHPIZE_DEPS && \
    docker-php-ext-install pdo pdo_pgsql pgsql && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install gd && \
    docker-php-ext-install zip && \
    docker-php-ext-install pcntl && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
# Download and install PIE
RUN curl -sSLf -o /usr/local/bin/pie https://github.com/php/pie/releases/latest/download/pie.phar \
    && chmod +x /usr/local/bin/pie
RUN pie install phpredis/phpredis
RUN pie install xdebug/xdebug

COPY ./docker/app/php.ini /usr/local/etc/php/conf.d/php.ini
COPY ./docker/app/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

USER root

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN groupmod -o -g ${PGID} www-data && \
    usermod -o -u ${PUID} -g www-data www-data

WORKDIR /var/www

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
