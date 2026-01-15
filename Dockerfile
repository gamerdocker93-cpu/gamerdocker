FROM php:8.2-apache

RUN apt-get update && apt-get install -y libpq-dev libicu-dev libzip-dev zip unzip git postgresql-client && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath
RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . .

# CREDENCIAIS FIXAS
ENV APP_KEY=base64:uS68On6HInL6p9G6nS8z2mB1vC4xR7zN0jK3lM6pQ9w=
ENV APP_DEBUG=true
ENV APP_ENV=production
ENV DB_CONNECTION=pgsql
ENV DB_HOST=dpg-d5ilblkhg0os738mds90-a.oregon-postgres.render.com
ENV DB_DATABASE=gamedocker
ENV DB_USERNAME=gamedocker_user
ENV DB_PASSWORD=79ICALvAosgFplyYmwc3QK4gtMhfrZlC

# LIMPEZA FÍSICA RADICAL: Apaga qualquer cache que você tenha enviado no GitHub
RUN rm -rf bootstrap/cache/*.php \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# SCRIPT DE ESTABILIZAÇÃO TOTAL - VERSÃO FINAL
RUN echo '#!/bin/sh\n\
# 1. Limpeza e Sincronização de Ambiente\n\
rm -rf bootstrap/cache/*.php\n\
cp .env.example .env\n\
\n\
# 2. Mata o cache do Composer para evitar tabelas fantasmas\n\
composer dump-autoload --optimize\n\
\n\
# 3. CONSTRUÇÃO TOTAL (Onde as faixas vermelhas somem)\n\
# O fresh garante que a tabela game_exclusives seja criada do zero antes da alteração\n\
php artisan migrate:fresh --force --seed\n\
\n\
# 4. TRANCA O LOG (Cria os caches antes do Apache subir)\n\
php artisan config:cache\n\
php artisan route:cache\n\
\n\
# 5. Permissões finais de escrita\n\
chown -R www-data:www-data storage bootstrap/cache\n\
\n\
echo "LOG LIMPO: O banco de dados foi sincronizado com sucesso."\n\
apache2-foreground' > /usr/local/bin/start-app.sh

RUN chmod +x /usr/local/bin/start-app.sh
CMD ["/usr/local/bin/start-app.sh"]
