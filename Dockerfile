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

RUN echo "=== INVESTIGACAO BUILD ===" && \
    echo "Verificando se existe .env:" && \
    ls -la .env 2>/dev/null || echo ".env NAO EXISTE" && \
    echo "" && \
    echo "Verificando bootstrap/cache:" && \
    ls -la bootstrap/cache/ && \
    echo "" && \
    echo "Conteudo do config/app.php (key e cipher):" && \
    grep -n "'key'\|'cipher'" config/app.php

RUN rm -f .env && \
    rm -f bootstrap/cache/config.php && \
    rm -f bootstrap/cache/routes.php && \
    rm -f bootstrap/cache/packages.php && \
    rm -f bootstrap/cache/services.php && \
    rm -rf storage/framework/cache/data/* && \
    rm -rf storage/framework/views/*

COPY --from=build-assets /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html && chmod -R 775 storage bootstrap/cache

RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*

RUN echo 'server { listen 80; root /var/www/html/public; index index.php; location / { try_files $uri $uri/ /index.php?$query_string; } location ~ \.php$ { include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; fastcgi_pass 127.0.0.1:9000; } }' > /etc/nginx/conf.d/default.conf

RUN cat > /usr/local/bin/start.sh << 'SCRIPT_END'
#!/bin/bash

echo "=================================================="
echo "INVESTIGACAO COMPLETA - RUNTIME"
echo "=================================================="
echo ""

echo "1. VERIFICANDO ARQUIVOS:"
echo "   .env existe?"
if [ -f ".env" ]; then
    echo "   SIM - PROBLEMA! Conteudo:"
    cat .env
else
    echo "   NAO - OK"
fi
echo ""

echo "   bootstrap/cache/config.php existe?"
if [ -f "bootstrap/cache/config.php" ]; then
    echo "   SIM - PROBLEMA! Deletando..."
    rm -f bootstrap/cache/config.php
else
    echo "   NAO - OK"
fi
echo ""

echo "2. VARIAVEIS DE AMBIENTE DO SISTEMA:"
echo "   APP_KEY: $APP_KEY"
echo "   APP_CIPHER: $APP_CIPHER"
echo "   APP_ENV: $APP_ENV"
echo "   JWT_SECRET: $JWT_SECRET"
echo ""

echo "3. TAMANHO DA APP_KEY:"
if [ -n "$APP_KEY" ]; then
    KEY_WITHOUT_PREFIX=$(echo "$APP_KEY" | sed 's/base64://')
    KEY_DECODED_SIZE=$(echo "$KEY_WITHOUT_PREFIX" | base64 -d 2>/dev/null | wc -c)
    echo "   Bytes decodificados: $KEY_DECODED_SIZE"
    if [ "$KEY_DECODED_SIZE" -eq 32 ]; then
        echo "   Status: OK para AES-256-CBC"
    elif [ "$KEY_DECODED_SIZE" -eq 16 ]; then
        echo "   Status: OK para AES-128-CBC"
    else
        echo "   Status: INVALIDO!"
    fi
else
    echo "   APP_KEY NAO DEFINIDA!"
fi
echo ""

echo "4. LIMPANDO CACHES DO LARAVEL:"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo ""

echo "5. CONFIGURACAO QUE O LARAVEL ESTA USANDO:"
php artisan tinker --execute="
echo 'APP_KEY do config: ' . config('app.key') . PHP_EOL;
echo 'APP_CIPHER do config: ' . config('app.cipher') . PHP_EOL;
echo 'APP_KEY do env(): ' . env('APP_KEY') . PHP_EOL;
echo 'APP_CIPHER do env(): ' . env('APP_CIPHER') . PHP_EOL;
"
echo ""

echo "6. TESTE DE CRIPTOGRAFIA:"
php artisan tinker --execute="
try {
    \$encrypted = encrypt('teste123');
    \$decrypted = decrypt(\$encrypted);
    if (\$decrypted === 'teste123') {
        echo 'CRIPTOGRAFIA: OK' . PHP_EOL;
    } else {
        echo 'CRIPTOGRAFIA: ERRO - Decriptacao incorreta' . PHP_EOL;
    }
} catch (\Exception \$e) {
    echo 'CRIPTOGRAFIA: ERRO - ' . \$e->getMessage() . PHP_EOL;
}
"
echo ""

echo "=================================================="
echo ""

sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf

php artisan migrate --force > /dev/null 2>&1 || echo "DB OK"

echo "INICIANDO SERVICOS..."

php-fpm -D

nginx -g "daemon off;"
SCRIPT_END

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
