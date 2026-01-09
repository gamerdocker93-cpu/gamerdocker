FROM php:8.2-apache

# 1. Instalar dependências e extensões EXIGIDAS pelo seu log (intl, zip, bcmath, etc)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_pgsql pgsql gd intl zip bcmath

# 2. Habilitar mod_rewrite do Apache
RUN a2enmod rewrite

# 3. Diretório de trabalho
WORKDIR /var/www/html

# 4. Copiar arquivos
COPY . .

# 5. Instalar Composer (forçando instalação mesmo com avisos de plataforma)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs

# 6. Configurar permissões essenciais para o Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Apontar o site para a pasta /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 8. O COMANDO PARA O BANCO: Migra as tabelas e inicia o site
CMD php artisan migrate --force && apache2-foreground




