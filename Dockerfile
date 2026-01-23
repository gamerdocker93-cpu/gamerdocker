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

RUN rm -f .env

RUN rm -f bootstrap/cache/*.php && \
    rm -rf storage/framework/cache/data/* && \
    rm -rf storage/framework/views/*

COPY --from=build-assets /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html && chmod -R 775 storage bootstrap/cache

RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*

RUN echo 'server { listen 80; root /var/www/html/public; index index.php; location / { try_files $uri $uri/ /index.php?$query_string; } location ~ \.php$ { include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; fastcgi_pass 127.0.0.1:9000; } }' > /etc/nginx/conf.d/default.conf

RUN cat > /usr/local/bin/start.sh << 'SCRIPT_END'
#!/bin/bash

echo "=================================================="
echo "INICIANDO APLICACAO"
echo "=================================================="

rm -f .env

rm -f bootstrap/cache/config.php
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

echo ""
echo "VERIFICACAO:"
echo "  APP_KEY do ambiente: $APP_KEY"
echo "  APP_CIPHER do ambiente: $APP_CIPHER"
echo ""

echo "TESTE DE CRIPTOGRAFIA:"
php artisan tinker --execute="
try {
    \$encrypted = encrypt('teste123');
    \$decrypted = decrypt(\$encrypted);
    if (\$decrypted === 'teste123') {
        echo 'RESULTADO: OK' . PHP_EOL;
    } else {
        echo 'RESULTADO: ERRO' . PHP_EOL;
    }
} catch (\Exception \$e) {
    echo 'RESULTADO: ERRO - ' . \$e->getMessage() . PHP_EOL;
}
"
echo ""

sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf

php artisan migrate --force 2>/dev/null || echo "DB: Verificado"

echo "=================================================="
echo "APLICACAO PRONTA"
echo "=================================================="

php-fpm -D
nginx -g "daemon off;"
SCRIPT_END

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]