# ===============================
# ESTÁGIO 1: Build dos Assets (Node.js)
# ===============================
FROM node:18-alpine AS build-assets
WORKDIR /app
COPY . .
# Instalamos as dependências e compilamos o Vue 3 / Vite
RUN npm install && npm run build

# ===============================
# ESTÁGIO 2: Aplicação (PHP + Nginx)
# ===============================
FROM php:8.2-fpm

# Dependências do Sistema
RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev libicu-dev libzip-dev \
    zip unzip git \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql intl zip bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configuração do PHP-FPM
RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf

# Diretório da Aplicação
WORKDIR /var/www/html
COPY . .

# Copiamos os assets compilados do estágio anterior
COPY --from=build-assets /app/public/build ./public/build

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissões do Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# NGINX — Limpeza Total de Conflitos
# Removemos tudo de sites-enabled e conf.d para evitar o erro de "conflicting server name"
RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/sites-available/* /etc/nginx/conf.d/*

# Configuração do Nginx (Usando porta 80 como base, será trocada no start)
RUN printf "server {\n\
    listen 80;\n\
    listen [::]:80;\n\
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

# Script de Inicialização (Entrypoint)
RUN echo '#!/bin/sh\n\
# 1. Ajusta a porta do Nginx para a porta da Railway ($PORT ou 8080)\n\
REAL_PORT=${PORT:-8080}\n\
echo "Configurando Nginx para ouvir na porta $REAL_PORT"\n\
sed -i "s/listen 80;/listen $REAL_PORT;/g" /etc/nginx/conf.d/default.conf\n\
sed -i "s/listen \[::\]:80;/listen [::]:$REAL_PORT;/g" /etc/nginx/conf.d/default.conf\n\
\n\
# 2. Limpeza de caches do Laravel\n\
php artisan config:clear\n\
php artisan cache:clear\n\
php artisan view:clear\n\
\n\
# 3. Executa Migrations (Opcional, mas recomendado para iGaming)\n\
echo "Rodando migrations..."\n\
php artisan migrate --force\n\
\n\
# 4. Inicia os serviços\n\
php-fpm -D\n\
nginx -g "daemon off;"' > /usr/local/bin/start-app.sh && chmod +x /usr/local/bin/start-app.sh

# Expõe a porta padrão
EXPOSE 8080

CMD ["/usr/local/bin/start-app.sh"]