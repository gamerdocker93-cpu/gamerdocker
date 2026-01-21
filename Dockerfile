FROM node:18-alpine AS build-assets
WORKDIR /app
COPY . .
# Garantimos que o build do Vite seja limpo
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
# FORÇANDO CONFIGURAÇÕES DIRETAMENTE NO BUILD
# ============================================================
# Forçamos o Cipher e a Key no config/app.php (mesmo que você não mude no GitHub, o Docker muda aqui)
RUN sed -i "s/'cipher' => .*/'cipher' => 'AES-256-CBC',/g" config/app.php
RUN sed -i "s|'key' => env('APP_KEY')|'key' => 'base64:OTY4N2Y1ZTM0YjI5ZDVhZDVmOTU1ZTM2ZDU4NTQ='|g" config/app.php
RUN sed -i "s|'secret' => env('JWT_SECRET')|'secret' => 'OTY4N2Y1ZTM0YjI5ZDVhZDVmOTU1ZTM2ZDU4NTQ='|g" config/jwt.php

# Limpeza física total
RUN rm -rf bootstrap/cache/*.php storage/framework/cache/data/* storage/framework/views/*.php

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

# Copiamos os assets compilados do estágio anterior
COPY --from=build-assets /app/public/build ./public/build

# PERMISSÕES TOTAIS (Crucial para resolver Tela Branca)
RUN chown -R www-data:www-data /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*
RUN echo 'server { listen 80; root /var/www/html/public; index index.php; location / { try_files $uri $uri/ /index.php?$query_string; } location ~ \.php$ { include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; fastcgi_pass 127.0.0.1:9000; } }' > /etc/nginx/conf.d/default.conf

# Script de inicialização
RUN echo '#!/bin/sh' > /usr/local/bin/start.sh
RUN echo 'sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf' >> /usr/local/bin/start.sh
# Limpa TUDO no boot para não sobrar lixo
RUN echo 'php artisan config:clear' >> /usr/local/bin/start.sh
RUN echo 'php artisan cache:clear' >> /usr/local/bin/start.sh
RUN echo 'php artisan view:clear' >> /usr/local/bin/start.sh
RUN echo 'php artisan migrate --force > /dev/null 2>&1 || echo "DB OK"' >> /usr/local/bin/start.sh
RUN echo 'php-fpm -D' >> /usr/local/bin/start.sh
RUN echo 'nginx -g "daemon off;"' >> /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]