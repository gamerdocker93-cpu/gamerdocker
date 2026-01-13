FROM php:8.2-apache

RUN apt-get update && apt-get install -y libpq-dev libicu-dev libzip-dev zip unzip git postgresql-client && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath
RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . .

# --- CONFIGURAÇÃO CHUMBADA (ESTRATÉGIA DE FORÇA BRUTA) ---
ENV APP_KEY=base64:uS68On6HInL6p9G6nS8z2mB1vC4xR7zN0jK3lM6pQ9w=
ENV APP_DEBUG=true
ENV APP_ENV=production

ENV DB_CONNECTION=pgsql
ENV DB_HOST=dpg-d5ilblkhg0os738mds90-a
ENV DB_PORT=5432
ENV DB_DATABASE=gamedocker
ENV DB_USERNAME=gamedocker_user
ENV DB_PASSWORD=79ICALvAosgFplyYmwc3QK4gtMhfrZlC
# -------------------------------------------------------

# Limpeza e permissões
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data bootstrap/cache \
    && rm -rf bootstrap/cache/*.php \
    && chmod -R 777 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# SCRIPT DE BOOT: Força o Laravel a ler estas variáveis e ignorar caches
RUN echo '#!/bin/sh\n\
# Cria o .env físico com os dados chumbados para não ter erro de leitura\n\
echo "APP_KEY=${APP_KEY}" > .env\n\
echo "DB_CONNECTION=pgsql" >> .env\n\
echo "DB_HOST=${DB_HOST}" >> .env\n\
echo "DB_DATABASE=${DB_DATABASE}" >> .env\n\
echo "DB_USERNAME=${DB_USERNAME}" >> .env\n\
echo "DB_PASSWORD=${DB_PASSWORD}" >> .env\n\
\n\
php artisan config:clear\n\
php artisan cache:clear\n\
\n\
# Tenta rodar as migrações (se falhar, o site sobe mesmo assim para vermos o erro)\n\
php artisan migrate --force || echo "Aviso: Migração ignorada"\n\
\n\
apache2-foreground' > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh
CMD ["/usr/local/bin/start-app.sh"]