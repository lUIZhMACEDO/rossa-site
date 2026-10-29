# Production image for hosting the site (Render). Local development still uses Sail (docker-compose.yml).
FROM node:20-bookworm-slim AS assets
WORKDIR /app
COPY package.json ./
RUN npm install --no-audit --no-fund
COPY . .
RUN npm run build

FROM php:8.3-apache-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev libicu-dev libzip-dev libpng-dev unzip git python3 python3-numpy python3-scipy \
    && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath gd opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's#/var/www/html#${APACHE_DOCUMENT_ROOT}#g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's#AllowOverride None#AllowOverride All#g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . .
COPY --from=assets /app/public/build public/build
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && mkdir -p storage/logs storage/framework/sessions storage/framework/views storage/framework/cache bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY deploy/start.sh /usr/local/bin/start-site
RUN chmod +x /usr/local/bin/start-site
CMD ["start-site"]
