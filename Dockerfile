# Dockerfile para Laravel 10 + Vue 3 no Railway

FROM node:18-alpine AS build-assets

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

RUN npm run build

FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    zip unzip git \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql intl zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY database/ database/
COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --ignore-platform-reqs --no-autoloader

COPY . .

RUN composer dump-autoload --optimize

RUN echo "--- BUSCANDO ARQUIVOS COM AES-128-CBC ---" && \
    grep -r "AES-128-CBC" . || echo "Nenhum arquivo encontrado"

RUN find . -type f -name "*.php" -exec sed -i 's/AES-128-CBC/aes-256-cbc/g' {} +
RUN find . -type f -name "*.php" -exec sed -i 's/AES-256-CBC/aes-256-cbc/g' {} +

RUN sed -i "s/'cipher' => 'AES-128-CBC'/'cipher' => env('APP_CIPHER', 'aes-256-cbc')/g" config/app.php
RUN sed -i "s/'cipher' => 'AES-256-CBC'/'cipher' => env('APP_CIPHER', 'aes-256-cbc')/g" config/app.php
RUN sed -i "s/'cipher' => 'aes-128-cbc'/'cipher' => env('APP_CIPHER', 'aes-256-cbc')/g" config/app.php

RUN php artisan config:clear || true
RUN php artisan view:clear || true

RUN rm -rf bootstrap/cache/*.php storage/framework/cache/data/*

COPY --from=build-assets /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html && chmod -R 775 storage bootstrap/cache

RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*

RUN echo 'server { listen 80; root /var/www/html/public; index index.php; location / { try_files $uri $uri/ /index.php?$query_string; } location ~ \.php$ { include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; fastcgi_pass 127.0.0.1:9000; } }' > /etc/nginx/conf.d/default.conf

RUN echo '#!/bin/sh' > /usr/local/bin/start.sh

RUN echo 'echo "--- VERIFICANDO VARIAVEIS DE AMBIENTE ---"' >> /usr/local/bin/start.sh
RUN echo 'echo "APP_ENV: ${APP_ENV}"' >> /usr/local/bin/start.sh
RUN echo 'echo "APP_CIPHER: ${APP_CIPHER}"' >> /usr/local/bin/start.sh
RUN echo 'echo "-------------------------------------------"' >> /usr/local/bin/start.sh

RUN echo 'sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf' >> /usr/local/bin/start.sh

RUN echo 'php artisan config:clear' >> /usr/local/bin/start.sh

RUN echo 'php artisan migrate --force > /dev/null 2>&1 || echo "DB OK"' >> /usr/local/bin/start.sh

RUN echo 'php-fpm -D' >> /usr/local/bin/start.sh

RUN echo 'nginx -g "daemon off;"' >> /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]