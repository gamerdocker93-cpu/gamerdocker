FROM php:8.2-apache

# 1. Instalar as extensões vitais que o seu log pediu (intl e zip)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_pgsql pgsql intl zip

# 2. Configurar o Apache
RUN a2enmod rewrite

# 3. Preparar o diretório
WORKDIR /var/www/html
COPY . .

# 4. Instalar o Composer ignorando tudo que trava o deploy
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# 5. Permissões brutas para evitar erro de acesso
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 6. Apontar o servidor para a pasta /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# 7. O COMANDO MESTRE: Limpa, Migra e Liga
# O sleep 5 dá tempo para o banco respirar antes de criar a tabela settings
CMD php artisan config:clear ; sleep 5 ; php artisan migrate --force ; apache2-foreground
