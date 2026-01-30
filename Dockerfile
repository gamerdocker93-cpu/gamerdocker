# ============================
# BUILD FRONTEND (VITE)
# ============================
FROM node:18-alpine AS build-assets

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .

# Força modo produção
ENV NODE_ENV=production

# Limpa builds antigos
RUN rm -rf public/build

RUN npm run build


# ============================
# BACKEND (PHP + NGINX)
# ============================
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    nginx \
    libicu-dev \
    libzip-dev \
    zip unzip git \
    libpng-dev libjpeg-dev libfreetype6-dev \
    bash \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql intl zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*


# Não limpar ENV
RUN sed -i 's/;clear_env = no/clear_env = no/g; s/clear_env = yes/clear_env = no/g' /usr/local/etc/php-fpm.d/www.conf


RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf


WORKDIR /var/www/html


# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache


COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader


COPY . .


RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true


# ============================
# COPIA BUILD DO VITE
# ============================
COPY --from=build-assets /app/public/build ./public/build


# Permissões
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 775 storage bootstrap/cache


# ============================
# NGINX
# ============================
RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*

RUN printf '%s\n' \
'server {' \
'  listen 80;' \
'  root /var/www/html/public;' \
'  index index.php;' \
'  location / {' \
'    try_files $uri $uri/ /index.php?$query_string;' \
'  }' \
'  location ~ \.php$ {' \
'    include fastcgi_params;' \
'    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' \
'    fastcgi_pass 127.0.0.1:9000;' \
'  }' \
'}' > /etc/nginx/conf.d/default.conf


# ============================
# START.SH
# ============================
RUN cat > /usr/local/bin/start.sh << 'EOF'
#!/bin/bash
set -e

echo "=================================="
echo "INICIANDO LARAVEL"
echo "=================================="


# Porta Railway
sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf


# Remove hot reload
rm -f public/hot
rm -f public/build/hot


# Remove cache antigo
rm -rf bootstrap/cache/*.php


# Limpa caches Laravel
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true


# Recria cache config (importante)
php artisan config:cache || true


# Verifica build
echo "=== BUILD VITE ==="
ls -la public/build || true
ls -la public/build/assets || true


# Migrations
if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
  php artisan migrate --force || true
fi


echo "=================================="
echo "APP ONLINE"
echo "=================================="


php-fpm -D
nginx -g "daemon off;"
EOF


RUN chmod +x /usr/local/bin/start.sh


EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]