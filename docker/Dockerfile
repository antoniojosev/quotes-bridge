FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        nginx \
        supervisor \
        nodejs \
        npm \
        git \
        unzip \
        libzip-dev \
    && docker-php-ext-install zip opcache \
    && mkdir -p /var/log/nginx /run/nginx /var/www/host

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/host

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

ENV COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
