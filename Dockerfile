FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev libicu-dev libzip-dev zip unzip git \
    && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath

RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# FORÇA BRUTA: Define uma chave temporária para o Laravel aceitar a cifra e depois gera uma definitiva
ENTRYPOINT ["/bin/sh", "-c", "export APP_KEY=base64:uS9vUvD5f5z6HkL9yX5TjN3bV8mK1pZ4qR2wE0aX7sY= && php artisan key:generate --force && php artisan jwt:secret --force && php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear && php artisan migrate --force && apache2-foreground"]
