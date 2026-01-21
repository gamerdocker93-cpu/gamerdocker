FROM php:8.1-fpm

# Instala dependências do sistema e bibliotecas necessárias para as extensões PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql gd bcmath zip intl mbstring

# Define o diretório de trabalho
WORKDIR /var/www

# Copia os arquivos do projeto para o container
COPY . .

# Instala o Composer vindo da imagem oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instala dependências ignorando travas de plataforma para evitar o erro do log
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Ajusta permissões de pastas críticas para o Laravel funcionar no Railway
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Comando para iniciar a aplicação na porta fornecida pelo Railway
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}