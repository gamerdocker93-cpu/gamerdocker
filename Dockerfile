FROM node:18-alpine AS build-assets
WORKDIR /app
COPY . .
RUN npm install && npm run build

FROM php:8.2-fpm
RUN apt-get update && apt-get install -y nginx libpq-dev libicu-dev libzip-dev zip unzip git libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql intl zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf
WORKDIR /var/www/html
COPY . .
COPY --from=build-assets /app/public/build ./public/build
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ============================================================
# AÇÃO CRÍTICA: Sobrescreve a chave direto no arquivo de config
# Isso remove qualquer dependência de .env ou cache para a chave
# ============================================================
RUN sed -i "s/'key' => env('APP_KEY')/'key' => 'base64:uS68On6HInL6p9G6nS8z2mB1vC4xR7zN0jK3lM6pQ9w='/g" config/app.php

RUN chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache
RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*

RUN printf "server {\n listen 80;\n root /var/www/html/public;\n index index.php;\n location / {\n try_files \$uri \$uri/ /index.php?\$query_string;\n }\n location ~ \.php$ {\n include fastcgi_params;\n fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n fastcgi_pass 127.0.0.1:9000;\n }\n}\n" > /etc/nginx/conf.d/default.conf

RUN echo '#!/bin/sh\n\
sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf\n\
# Limpeza total antes de iniciar\n\
rm -rf bootstrap/cache/*.php\n\
php artisan config:clear\n\
php artisan cache:clear\n\
# Tenta rodar migrations (ignora erro de coluna duplicada)\n\
php artisan migrate --force || echo "Migrations já executadas"\n\
php-fpm -D && nginx -g "daemon off;"' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]