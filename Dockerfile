FROM php:8.2-apache

# 1. Instalação de dependências e extensões PHP para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    postgresql-client \
    && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath

# Habilita o mod_rewrite do Apache para o Laravel
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . .

# 2. Configurações de Ambiente
ENV APP_DEBUG=false
ENV APP_ENV=production
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# 3. Ajuste do Apache para ler a pasta /public e aceitar a porta da Railway
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && echo "<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" >> /etc/apache2/apache2.conf

# Faz o Apache ouvir a porta dinâmica da Railway ($PORT)
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g' /etc/apache2/sites-available/000-default.conf

# 4. Instalação das dependências do PHP (Composer)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# Ajuste de permissões para as pastas do Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# 5. Script de inicialização (Entrypoint otimizado)
RUN echo '#!/bin/sh\n\
if [ ! -f .env ]; then\n\
    cp .env.example .env\n\
fi\n\
\n\
# Força a limpeza de caches para garantir que as variáveis da Railway sejam lidas\n\
php artisan config:clear\n\
php artisan cache:clear\n\
\n\
# Tenta rodar as migrações\n\
php artisan migrate --force\n\
\n\
# Otimiza novamente para produção\n\
php artisan config:cache\n\
php artisan route:cache\n\
\n\
echo "SISTEMA ONLINE NA RAILWAY"\n\
exec apache2-foreground' > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh

# Exposição da porta dinâmica
EXPOSE ${PORT}

CMD ["/usr/local/bin/start-app.sh"]


