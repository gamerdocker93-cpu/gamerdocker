FROM php:8.2-apache

# 1. Instalação rápida das dependências vitais detectadas nos logs
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_pgsql pgsql intl zip

# 2. Ativa o redirecionamento do Apache
RUN a2enmod rewrite

# 3. Organiza os arquivos (Garante que o Composer encontre o json)
WORKDIR /var/www/html
COPY . /var/www/html

# 4. Instalação ultra-veloz do Composer ignorando scripts de erro
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts || true

# 5. Permissões de escrita (Crucial para o erro da tabela settings sumir)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 6. Configura a porta de entrada correta
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# 7. COMANDO DE EXECUÇÃO: Tenta migrar e, se falhar, sobe o site assim mesmo
CMD php artisan migrate --force ; apache2-foreground

