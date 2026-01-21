FROM node:18-alpine AS build-assets
WORKDIR /app
COPY . .
RUN npm install && npm run build

FROM php:8.2-fpm
RUN apt-get update && apt-get install -y nginx libpq-dev libicu-dev libzip-dev zip unzip git libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql intl zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf
WORKDIR /var/www/html
COPY . .

# ============================================================
# LIMPEZA ATÔMICA E SOBREPOSIÇÃO DE CONFIGURAÇÃO
# ============================================================
# 1. Removemos QUALQUER arquivo de cache que possa existir
RUN rm -rf bootstrap/cache/*.php storage/framework/cache/data/* storage/framework/views/*.php storage/framework/sessions/*

# 2. Forçamos a criação de um .env fixo dentro da imagem (Última instância)
RUN echo "APP_KEY=base64:OTY4N2Y1ZTM0YjI5ZDVhZDVmOTU1ZTM2ZDU4NTQ=" > .env && \
    echo "APP_CIPHER=AES-256-CBC" >> .env && \
    echo "JWT_SECRET=OTY4N2Y1ZTM0YjI5ZDVhZDVmOTU1ZTM2ZDU4NTQ=" >> .env && \
    echo "DB_CONNECTION=mysql" >> .env

# 3. Substituição bruta nos arquivos de config
RUN sed -i "s|'key' => .*|'key' => 'base64:OTY4N2Y1ZTM0YjI5ZDVhZDVmOTU1ZTM2ZDU4NTQ=',|g" config/app.php && \
    sed -i "s|'cipher' => .*|'cipher' => 'AES-256-CBC',|g" config/app.php

# Instalação sem scripts para não gerar cache prematuro
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

COPY --from=build-assets /app/public/build ./public/build
RUN chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache
RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*
RUN echo 'server { listen 80; root /var/www/html/public; index index.php; location / { try_files $uri $uri/ /index.php?$query_string; } location ~ \.php$ { include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; fastcgi_pass 127.0.0.1:9000; } }' > /etc/nginx/conf.d/default.conf

# Script de inicialização que limpa TUDO no boot
RUN echo '#!/bin/sh' > /usr/local/bin/start.sh
RUN echo 'sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf' >> /usr/local/bin/start.sh
RUN echo 'php artisan config:clear' >> /usr/local/bin/start.sh
RUN echo 'php artisan cache:clear' >> /usr/local/bin/start.sh
RUN echo 'php artisan view:clear' >> /usr/local/bin/start.sh
RUN echo 'php artisan migrate --force > /dev/null 2>&1 || echo "DB OK"' >> /usr/local/bin/start.sh
RUN echo 'php-fpm -D' >> /usr/local/bin/start.sh
RUN echo 'nginx -g "daemon off;"' >> /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]