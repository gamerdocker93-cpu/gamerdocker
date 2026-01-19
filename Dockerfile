FROM php:8.2-fpm

# ===============================
# Dependências do Sistema
# ===============================
RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev libicu-dev libzip-dev \
    zip unzip git \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql intl zip bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ===============================
# Configuração do PHP-FPM
# ===============================
# Ajustamos para ouvir em 127.0.0.1:9000 para comunicação interna com Nginx
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
# Permissões do Laravel
# ===============================
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# ===============================
# NGINX — Limpeza e Configuração
# ===============================
# Removemos configurações padrão que causam conflito (o erro "conflicting server name")
RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/sites-available/* /etc/nginx/conf.d/*

# Criamos a configuração do Nginx usando a variável $PORT da Railway
RUN printf "server {\n\
    listen 80;\n\
    listen [::]:80;\n\
    # Railway usa a porta 80 internamente por padrão, mas injeta \$PORT\n\
    # Se você configurar a porta 80 na Railway, este arquivo funcionará.\n\
    \n\
    root /var/www/html/public;\n\
    index index.php index.html;\n\
    server_name _;\n\
\n\
    location / {\n\
        try_files \$uri \$uri/ /index.php?\$query_string;\n\
    }\n\
\n\
    location ~ \.php$ {\n\
        include fastcgi_params;\n\
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
    }\n\
}\n" > /etc/nginx/conf.d/default.conf

# ===============================
# Script de Inicialização (Entrypoint)
# ===============================
# Usamos um script para garantir que o Nginx use a porta correta da Railway
RUN echo '#!/bin/sh\n\
# Substitui a porta 80 pela porta fornecida pela Railway ($PORT)\n\
sed -i "s/listen 80;/listen ${PORT:-80};/g" /etc/nginx/conf.d/default.conf\n\
\n\
php artisan config:clear\n\
php artisan cache:clear\n\
\n\
# Inicia PHP-FPM em background e Nginx em foreground\n\
php-fpm -D\n\
nginx -g "daemon off;"' > /usr/local/bin/start-app.sh && chmod +x /usr/local/bin/start-app.sh

# Railway expõe a porta via variável de ambiente $PORT
EXPOSE 80

CMD ["/usr/local/bin/start-app.sh"]
