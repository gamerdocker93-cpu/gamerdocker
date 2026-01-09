FROM php:8.2-apache

# 1. Instala apenas o motor essencial para o banco de dados (Rápido)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_pgsql pgsql

# 2. Habilita o redirecionamento de links do site
RUN a2enmod rewrite

# 3. Define onde o site vai morar
WORKDIR /var/www/html
COPY . .

# 4. COMPOSER ULTRA-RÁPIDO: Pula todas as verificações que causam o "Timeout"
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# 5. Garante que o site tenha permissão de escrita
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 6. Configura a porta de entrada para a pasta correta (/public)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# 7. O COMANDO DE OURO: Cria o banco e liga o site, custe o que custar
CMD php artisan migrate --force ; php artisan db:seed --force ; apache2-foreground
