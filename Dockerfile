FROM php:8.2-fpm

# Dependências do sistema
RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev libicu-dev libzip-dev \
    zip unzip git \
    libpng-dev libjpeg-dev libfreetype6-dev \
    gettext-base \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql intl zip bcmath gd

# Config PHP-FPM
RUN sed -i 's/listen = .*/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/zz-docker.conf

# Diretório da aplicação
WORKDIR /var/www/html
COPY . .

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissões Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Template Nginx (PORT dinâmico)
RUN echo 'server { \
    listen ${PORT}; \
    server_name _; \
    root /var/www/html/public; \
    index index.php; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
}' > /etc/nginx/conf.d/default.conf.template

# Script de start
RUN echo '#!/bin/sh \
envsubst "\$PORT" < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf \
php-fpm -D \
nginx -g "daemon off;"' > /start.sh \
    && chmod +x /start.sh

RUN php artisan config:clear \
 && php artisan route:clear \
 && php artisan view:clear

CMD ["/start.sh"]

