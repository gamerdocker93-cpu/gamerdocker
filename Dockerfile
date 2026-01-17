FROM php:8.2-fpm

# ===============================
# Dependências do sistema
# ===============================
RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev libicu-dev libzip-dev \
    zip unzip git \
    libpng-dev libjpeg-dev libfreetype6-dev \
    gettext-base \
    && rm -rf /usr/share/nginx/html/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql intl zip bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ===============================
# PHP-FPM
# ===============================
RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf

# ===============================
# Aplicação
# ===============================
WORKDIR /var/www/html
COPY . .

# ===============================
# Composer
# ===============================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ===============================
# Permissões Laravel
# ===============================
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# ===============================
# NGINX — remove default
# ===============================
RUN rm -f /etc/nginx/sites-enabled/default \
 && rm -f /etc/nginx/sites-available/default

# ===============================
# NGINX SITE (Railway PORT)
# ===============================
RUN printf 'server {\n\
    listen ${PORT};\n\
    server_name _;\n\
    root /var/www/html/public;\n\
    index index.php;\n\
\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
\n\
    location ~ \\.php$ {\n\
        include fastcgi_params;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
    }\n\
}\n' > /etc/nginx/sites-available/laravel.conf

# ===============================
# Enable site
# ===============================
RUN ln -s /etc/nginx/sites-available/laravel.conf /etc/nginx/sites-enabled/laravel.conf

# ===============================
# Start script
# ===============================
RUN printf '#!/bin/sh\n\
set -e\n\
\n\
envsubst "\\$PORT" < /etc/nginx/sites-available/laravel.conf > /etc/nginx/sites-enabled/laravel.conf\n\
\n\
php artisan config:clear || true\n\
php artisan cache:clear || true\n\
php artisan route:clear || true\n\
php artisan view:clear || true\n\
\n\
php-fpm -D\n\
nginx -g "daemon off;"\n' > /start.sh \
 && chmod +x /start.sh

CMD ["/start.sh"]
