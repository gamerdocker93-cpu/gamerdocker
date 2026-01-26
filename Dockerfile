FROM node:18-alpine AS build-assets
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build


FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    nginx \
    libicu-dev \
    libzip-dev \
    zip unzip git \
    libpng-dev libjpeg-dev libfreetype6-dev \
    bash \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql intl zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY database/ database/
COPY composer.json composer.lock ./

# Instala dependências (agora intl existe, então não precisa ignore-platform-reqs)
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

COPY . .

# Garante que .env físico não exista (Railway usa Variables)
RUN rm -f .env

RUN rm -f bootstrap/cache/*.php && \
    rm -rf storage/framework/cache/data/* && \
    rm -rf storage/framework/views/*

COPY --from=build-assets /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html && chmod -R 775 storage bootstrap/cache

RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*

RUN echo 'server { \
    listen 80; \
    root /var/www/html/public; \
    index index.php; \
    location / { try_files $uri $uri/ /index.php?$query_string; } \
    location ~ \.php$ { \
        include fastcgi_params; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        fastcgi_pass 127.0.0.1:9000; \
    } \
}' > /etc/nginx/conf.d/default.conf

RUN cat > /usr/local/bin/start.sh << 'EOF'
#!/bin/bash
set -e

echo "=================================================="
echo "INICIANDO APLICACAO LARAVEL"
echo "=================================================="

rm -f .env

php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf

# IMPORTANTE: NÃO rodar migrations automaticamente (seu DB já tem tabelas via SQL)
# php artisan migrate --force

echo "=================================================="
echo "APLICACAO PRONTA"
echo "=================================================="

php-fpm -D
nginx -g "daemon off;"
EOF

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]