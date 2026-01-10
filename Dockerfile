FROM php:8.2-apache

# 1. Instala dependências do sistema e extensões para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath

# 2. Ativa o módulo de reescrita do Apache
RUN a2enmod rewrite

# 3. Define o diretório de trabalho
WORKDIR /var/www/html

# 4. Copia todos os arquivos do projeto
COPY . .

# 5. Instala o Composer e as dependências do Laravel
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs

# 6. Ajusta permissões para as pastas de cache e storage
RUN chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache

# 7. Configura o Apache para a pasta /public do Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# 8. COMANDO FINAL: Limpa cache, reconstrói o banco e inicia o servidor
# O 'migrate:fresh' resolve o erro de tabela não encontrada que apareceu no log
ENTRYPOINT ["/bin/sh", "-c", "php artisan config:clear && php artisan migrate:fresh --force && php artisan db:seed --force ; apache2-foreground"]
