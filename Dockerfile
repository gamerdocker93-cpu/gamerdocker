FROM php:8.2-apache

# 1. Instalação agressiva de todas as dependências exigidas pelo seu log
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_pgsql pgsql gd intl zip bcmath mbstring xml

# 2. Habilitar mod_rewrite para as rotas do site funcionarem
RUN a2enmod rewrite

# 3. Definir diretório de trabalho
WORKDIR /var/www/html

# 4. Copiar os arquivos do seu repositório
COPY . .

# 5. Instalar o Composer ignorando restrições que travam o deploy gratuito
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs

# 6. Permissões críticas para o site não dar erro de "Acesso Negado"
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Apontar o servidor para a pasta correta (/public)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 8. O COMANDO FINAL: Cria as tabelas do banco e liga o site
# Isso resolve o erro "relation settings does not exist"
CMD php artisan migrate --force && apache2-foreground


