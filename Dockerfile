FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev libicu-dev libzip-dev zip unzip git \
    && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath

RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . .

# 1. EXPLODINDO O CACHE (Terra Arrasada)
RUN rm -rf .env bootstrap/cache/*.php storage/framework/sessions/* storage/framework/views/*.php storage/logs/*.log

# 2. INJEÇÃO FORÇADA DE CHAVE (O segredo do domínio)
# Este comando substitui o pedido de chave por uma chave real fixa direto no arquivo de configuração
RUN sed -i "s/'key' => env('APP_KEY'),/'key' => 'base64:OTY4N2Y1ZTM0YjI5ZDVhZDVmOTU1ZTM2ZDU4NTQ=',/g" config/app.php

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs

# 3. TRANCANDO O SISTEMA DE ARQUIVOS
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# 4. O COMANDO DE EXECUÇÃO (Obliteração de resistências)
ENTRYPOINT ["/bin/sh", "-c", " \
    php artisan config:clear && \
    php artisan cache:clear && \
    php artisan view:clear && \
    php artisan route:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan migrate --force && \
    apache2-foreground"]
