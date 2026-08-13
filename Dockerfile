#syntax=docker/dockerfile:1

FROM dunglas/frankenphp:1-php8.4

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip git \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions \
        pdo_mysql \
        intl \
        zip \
        opcache \
        apcu \
        gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# FrankenPHP image expects config here
COPY docker/Caddyfile /etc/frankenphp/Caddyfile
COPY docker/php.ini /usr/local/etc/php/conf.d/99-arca.ini
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint

RUN chmod +x /usr/local/bin/docker-entrypoint

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV SERVER_NAME=:80

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
