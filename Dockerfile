FROM php:8.2-apache

RUN apt-get update && apt-get install -y libpq-dev libicu-dev libzip-dev zip unzip git postgresql-client && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath
RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . .

# Garante permissões e limpa cache no build
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data bootstrap/cache \
    && rm -f bootstrap/cache/*.php \
    && chmod -R 777 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# SCRIPT DE EMERGÊNCIA: Prioriza subir o site, tenta migrar mas não trava o Apache
RUN echo '#!/bin/sh\n\
rm -f /var/www/html/bootstrap/cache/config.php\n\
php artisan config:clear\n\
php artisan cache:clear\n\
php artisan view:clear\n\
# Tentamos o migrate:fresh, mas se houver erro na tabela game_exclusives, o site sobe mesmo assim\n\
php artisan migrate:fresh --force || echo "Aviso: Erro nas migrações, mas o site continuará subindo..."\n\
apache2-foreground' > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh

CMD ["/usr/local/bin/start-app.sh"]


