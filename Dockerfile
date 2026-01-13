FROM php:8.2-apache

RUN apt-get update && apt-get install -y libpq-dev libicu-dev libzip-dev zip unzip git postgresql-client && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath
RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . .

# Garante que as pastas de cache existam e estejam limpas antes do build
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data bootstrap/cache \
    && rm -f bootstrap/cache/*.php \
    && chmod -R 777 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalação limpa das dependências
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# O SEGREDO ATUALIZADO: O comando 'migrate:fresh' limpa o banco e reconstrói tudo do zero
RUN echo '#!/bin/sh\n\
rm -f /var/www/html/bootstrap/cache/config.php\n\
php artisan config:clear\n\
php artisan cache:clear\n\
php artisan view:clear\n\
# Limpa as tabelas problemáticas e cria a estrutura correta\n\
php artisan migrate:fresh --force\n\
apache2-foreground' > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh

CMD ["/usr/local/bin/start-app.sh"]


