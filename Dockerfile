FROM php:8.2-apache

# 1. Instalação de dependências e extensões PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    postgresql-client \
    && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath

RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . .

# 2. Configurações de Ambiente (Limpas de dados da Render)
# Nota: APP_KEY e DB_HOST virão do painel da Railway
ENV APP_DEBUG=false
ENV APP_ENV=production

# 3. Limpeza Radical e Permissões
RUN rm -rf bootstrap/cache/*.php \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# 4. Instalação do Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# 5. Configuração do Apache para a pasta /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# 6. Script de inicialização otimizado para Railway
RUN echo '#!/bin/sh\n\
# Limpa caches antigos\n\
rm -rf bootstrap/cache/*.php\n\
\n\
# Cria o .env a partir do example (que agora está limpo)\n\
cp .env.example .env\n\
\n\
# Sincroniza o autoload do composer\n\
composer dump-autoload --optimize\n\
\n\
# RODA AS MIGRAÇÕES: Isso cria a tabela game_exclusives do zero no Postgres da Railway\n\
php artisan migrate:fresh --force --seed\n\
\n\
# Otimização de performance\n\
php artisan config:cache\n\
php artisan route:cache\n\
\n\
# Garante as permissões antes de subir o servidor\n\
chown -R www-data:www-data storage bootstrap/cache\n\
chmod -R 777 storage bootstrap/cache\n\
\n\
echo "SISTEMA ONLINE NA RAILWAY"\n\
apache2-foreground' > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh
CMD ["/usr/local/bin/start-app.sh"]

