FROM php:8.5-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress

COPY . .

EXPOSE 8091

CMD ["php", "-d", "display_errors=0", "-S", "0.0.0.0:8091", "-t", "public"]
