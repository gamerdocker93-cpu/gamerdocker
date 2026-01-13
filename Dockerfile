FROM php:8.2-apache

RUN apt-get update && apt-get install -y libpq-dev libicu-dev libzip-dev zip unzip git postgresql-client && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath
RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . .

# --- DADOS DO SEU BANCO (CHUMBADOS) ---
ENV APP_KEY=base64:uS68On6HInL6p9G6nS8z2mB1vC4xR7zN0jK3lM6pQ9w=
ENV DB_CONNECTION=pgsql
ENV DB_HOST=dpg-d5ilblkhg0os738mds90-a
ENV DB_DATABASE=gamedocker
ENV DB_USERNAME=gamedocker_user
ENV DB_PASSWORD=79ICALvAosgFplyYmwc3QK4gtMhfrZlC
# --------------------------------------

# 1. LIMPEZA TOTAL E PERMISSÕES
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data bootstrap/cache \
    && rm -rf bootstrap/cache/*.php \
    && chmod -R 777 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# 2. COMPOSER
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# 3. CIRURGIA NO ARQUIVO APP.PHP (O "PULO DO GATO")
# Este comando substitui a leitura da chave no config/app.php pela nossa chave fixa, eliminando o erro de Cipher para sempre.
RUN sed -i "s/'key' => env('APP_KEY')/'key' => 'base64:uS68On6HInL6p9G6nS8z2mB1vC4xR7zN0jK3lM6pQ9w='/g" config/app.php \
    && sed -i "s/'cipher' => env('APP_CIPHER', 'AES-256-CBC')/'cipher' => 'AES-256-CBC'/g" config/app.php

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# 4. SCRIPT DE INICIALIZAÇÃO
RUN echo '#!/bin/sh\n\
php artisan config:clear\n\
php artisan cache:clear\n\
# Força as migrações usando os dados chumbados\n\
php artisan migrate --force || echo "Migracao ignorada"\n\
apache2-foreground' > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh
CMD ["/usr/local/bin/start-app.sh"]