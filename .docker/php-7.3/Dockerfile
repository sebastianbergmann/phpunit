FROM php:7.3-cli

RUN apt-get update \
    && apt-get install --assume-yes \
        apt-utils \
        git \
        libzip-dev \
        zip \
        zlib1g-dev \
    && apt-get clean --assume-yes \
    && docker-php-ext-install \
        zip

RUN echo 'memory_limit=-1' >> /usr/local/etc/php/conf.d/docker-php-memory-limit.ini

COPY --from=composer:1.9.0 /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1
