#syntax=docker/dockerfile:1

FROM dunglas/frankenphp:1-php8.4

RUN install-php-extensions \
    pdo_mysql \
    intl \
    zip \
    opcache \
    apcu \
    gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/php.ini /usr/local/etc/php/conf.d/99-arca.ini
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint

RUN chmod +x /usr/local/bin/docker-entrypoint

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV SERVER_NAME=:80

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
