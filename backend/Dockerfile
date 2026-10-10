FROM php:8.3-cli

# Sistemski paketi + PHP ekstenzije koje Laravel treba za MySQL
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev ca-certificates \
    && docker-php-ext-install pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache \
    && sed -i 's/\r$//' docker-entrypoint.sh \
    && chmod +x docker-entrypoint.sh

# 4 radna procesa da API moze da odgovara na vise zahteva istovremeno
ENV PHP_CLI_SERVER_WORKERS=4
EXPOSE 10000

CMD ["./docker-entrypoint.sh"]
