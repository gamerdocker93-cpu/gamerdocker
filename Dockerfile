FROM php:8.2-apache

# Instalação completa das bibliotecas do PostgreSQL e extensões PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath

# Ativa o Rewrite do Apache para as rotas do Laravel funcionarem
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . .

# Instala o Composer e as dependências
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs

# Permissões de pasta para evitar erros de escrita
RUN chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache

# Configuração da pasta pública
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# O PULO DO GATO: Limpa cache e força a migração antes de ligar o site
ENTRYPOINT ["/bin/sh", "-c", "php artisan config:clear && php artisan cache:clear && php artisan migrate --force && apache2-foreground"]
