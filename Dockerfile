FROM php:8.2-apache

# 1. Instala apenas o essencial para o banco e zip (rápido)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_pgsql pgsql zip

# 2. Habilita o redirecionamento de rotas
RUN a2enmod rewrite

# 3. Define a pasta do projeto
WORKDIR /var/www/html
COPY . .

# 4. Instala o Composer IGNORANDO todas as verificações de plataforma
# Isso pula a parte que estava travando e dando Timeout
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs

# 5. Ajusta as permissões de pasta
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 6. Aponta o servidor para a pasta correta
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# 7. O COMANDO FINAL: Ignora erros e tenta subir o banco na marra
CMD php artisan migrate --force ; apache2-foreground

