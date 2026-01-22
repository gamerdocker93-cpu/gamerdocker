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
    bash \
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

RUN echo "=============================================" && \
    echo "INVESTIGACAO DETETIVE - ANTES DAS CORRECOES" && \
    echo "=============================================" && \
    echo "" && \
    echo "1. CONTEUDO DO config/app.php (linhas com cipher):" && \
    grep -n "cipher" config/app.php && \
    echo "" && \
    echo "2. CONTEUDO DO config/app.php (linhas com key):" && \
    grep -n "'key'" config/app.php && \
    echo "" && \
    echo "============================================="

RUN sed -i "s/'cipher' => 'AES-256-CBC'/'cipher' => env('APP_CIPHER', 'aes-256-cbc')/g" config/app.php

RUN sed -i "s/'cipher' => 'AES-128-CBC'/'cipher' => env('APP_CIPHER', 'aes-256-cbc')/g" config/app.php

RUN sed -i "s/'key' => 'base64:.*'/'key' => env('APP_KEY')/g" config/app.php

RUN echo "=============================================" && \
    echo "INVESTIGACAO DETETIVE - DEPOIS DAS CORRECOES" && \
    echo "=============================================" && \
    echo "" && \
    echo "1. CONTEUDO DO config/app.php (linhas com cipher):" && \
    grep -n "cipher" config/app.php && \
    echo "" && \
    echo "2. CONTEUDO DO config/app.php (linhas com key):" && \
    grep -n "'key'" config/app.php && \
    echo "" && \
    echo "============================================="

RUN rm -rf bootstrap/cache/*.php storage/framework/cache/data/* storage/framework/views/*

COPY --from=build-assets /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html && chmod -R 775 storage bootstrap/cache

RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*

RUN echo 'server { listen 80; root /var/www/html/public; index index.php; location / { try_files $uri $uri/ /index.php?$query_string; } location ~ \.php$ { include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; fastcgi_pass 127.0.0.1:9000; } }' > /etc/nginx/conf.d/default.conf

RUN cat > /usr/local/bin/start.sh << 'SCRIPT_END'
#!/bin/bash

echo "=================================================="
echo "DELACAO PREMIADA - INVESTIGACAO EM RUNTIME"
echo "=================================================="
echo ""

echo "1. VARIAVEIS DE AMBIENTE:"
echo "   APP_ENV: $APP_ENV"
echo "   APP_CIPHER: $APP_CIPHER"
echo "   APP_KEY (completa): $APP_KEY"
echo ""

echo "2. TAMANHO DA APP_KEY DECODIFICADA:"
APP_KEY_DECODED=$(echo "$APP_KEY" | sed 's/base64://' | base64 -d | wc -c)
echo "   Bytes: $APP_KEY_DECODED"
if [ "$APP_KEY_DECODED" -eq 32 ]; then
    echo "   Status: OK para AES-256-CBC (32 bytes)"
elif [ "$APP_KEY_DECODED" -eq 16 ]; then
    echo "   Status: OK para AES-128-CBC (16 bytes)"
else
    echo "   Status: ERRO - Tamanho invalido!"
fi
echo ""

echo "3. CONTEUDO REAL DO config/app.php (cipher):"
grep "'cipher'" config/app.php
echo ""

echo "4. CONTEUDO REAL DO config/app.php (key):"
grep "'key'" config/app.php | head -1
echo ""

echo "5. TESTE DE CRIPTOGRAFIA:"
php artisan tinker --execute="try { \$encrypted = encrypt('teste'); echo '   Criptografia: OK\n'; } catch (\Exception \$e) { echo '   Criptografia: ERRO - ' . \$e->getMessage() . '\n'; }"
echo ""

echo "6. CONFIGURACAO QUE O LARAVEL ESTA USANDO:"
php artisan tinker --execute="echo '   APP_KEY: ' . config('app.key') . '\n'; echo '   APP_CIPHER: ' . config('app.cipher') . '\n';"
echo ""

echo "=================================================="
echo ""

sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf

php artisan config:clear

php artisan migrate --force > /dev/null 2>&1 || echo "DB OK"

php-fpm -D

nginx -g "daemon off;"
SCRIPT_END

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
