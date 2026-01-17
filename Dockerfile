FROM php:8.2-fpm

# ===============================
# System deps
# ===============================
RUN apt-get update && apt-get install -y 
    nginx \
    libpq-dev libicu-dev libzip-dev \
    zip unzip git \
    libpng-dev libjpeg-dev libfreetype6- gettext-base \
    && docker-php-ext-configure gd --wit
    && docker-php-ext-install pdo_pgsql
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /usr/share/nginx/html/*

# ===============================
# PHP-FPM config
# ===============================
RUN sed -i 's|listen = .*|listen = 127.0

# ===============================
# App
# ===============================
WORKDIR /var/www/html
COPY . .

# ===============================
# Composer
# ===============================
COPY --from=composer:2 /usr/bin/composer
RUN composer install --no-dev --optimize

# ===============================
# File permissions Laravel
# ===============================
RUN chown -R www-data:www-data storage b
 && chmod -R 775 storage bootstrap/cache

# ===============================
# Nginx template (uses $PORT)
# ===============================
RUN printf 'server {\n\
    listen ${PORT};\n\
    server_name _;\n\
    root /var/www/html/public;\n\
    index index.php index.html;\n\
\n\
    location / {\n\
        try_files $uri $uri/ /index.php?
    }\n\
\n\
    location ~ \\.php$ {\n\
        include fastcgi_params;\n\
        fastcgi_param SCRIPT_FILENAME $d
        fastcgi_pass 127.0.0.1:9000;\n\
    }\n\
}\n' > /etc/nginx/conf.d/default.conf.te

# ===============================
# Startup
# ===============================
RUN printf '#!/bin/sh\n\
set -e\n\
\n\
# substitute PORT into config\n\
envsubst \"$PORT\" < /etc/nginx/conf.d/d \n\
# clear Laravel caches just in case\n\
php artisan config:clear || true\n\
php artisan cache:clear || true\n\
php artisan route:clear || true\n\
php artisan view:clear || true\n\
\n\
# start PHP-FPM then nginx\n\
php-fpm -D\n\
nginx\n' > /start.sh \
 && chmod +x /start.sh

CMD ["sh", "/start.sh"]
