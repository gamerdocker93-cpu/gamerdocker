# Usando a imagem oficial do PHP com Apache para Laravel
FROM php:8.2-apache

# 1. Instalar dependências do sistema e extensões PHP necessárias para PostgreSQL e Laravel
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_pgsql pgsql gd

# 2. Habilitar o mod_rewrite do Apache (essencial para rotas do Laravel)
RUN a2enmod rewrite

# 3. Definir o diretório de trabalho
WORKDIR /var/www/html

# 4. Copiar os arquivos do projeto para o container
COPY . .

# 5. Instalar o Composer (gerenciador de dependências do PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 6. Configurar permissões para as pastas de cache e storage (evita erro de permissão)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Ajustar o DocumentRoot do Apache para a pasta /public do Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 8. COMANDO MÁGICO: Roda as migrações e inicia o servidor
# O --force é obrigatório para rodar em produção na Render
CMD php artisan migrate --force && apache2-foreground







