FROM php:8.2-apache

# 1. Instalação agressiva de extensões vitais
RUN apt-get update && apt-get install -y \
    libpq-dev libicu-dev libzip-dev zip unzip git \
    && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath

RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . .

# 2. Composer com otimização máxima de autoloader (mata classes fantasmas)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs

# 3. Blindagem de Permissões (775 para garantir escrita de novos caches)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 4. Configuração do Document Root do Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# 5. O ENTRYPOINT MATADOR (A Estratégia Sênior)
# Aqui destruímos o arquivo .env (se existir), limpamos o cache físico do bootstrap
# e forçamos o Laravel a gerar as chaves e o cache de configuração no momento do boot.
ENTRYPOINT ["/bin/sh", "-c", " \
    rm -f .env && \
    find bootstrap/cache -type f -not -name '.gitignore' -delete && \
    find storage/framework/sessions -type f -delete && \
    find storage/framework/views -type f -delete && \
    find storage/framework/cache/data -type f -delete && \
    php artisan key:generate --force && \
    php artisan jwt:secret --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan migrate --force && \
    apache2-foreground"]
