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
    echo "1. CONTEUDO DO config/app.php (linhas 444-447):" && \
    sed -n '444,447p' config/app.php && \
    echo "" && \
    echo "2. BUSCANDO TODAS AS REFERENCIAS A 'cipher' NO ARQUIVO:" && \
    grep -n "cipher" config/app.php && \
    echo "" && \
    echo "3. BUSCANDO TODAS AS REFERENCIAS A 'key' NO ARQUIVO:" && \
    grep -n "'key'" config/app.php && \
    echo "============================================="

RUN sed -i "s/'cipher' => 'AES-256-CBC'/'cipher' => env('APP_CIPHER', 'aes-256-cbc')/g" config/app.php

RUN sed -i "s/'cipher' => 'AES-128-CBC'/'cipher' => env('APP_CIPHER', 'aes-256-cbc')/g" config/app.php

RUN sed -i "s/'key' => 'base64:.*'/'key' => env('APP_KEY')/g" config/app.php

RUN echo "=============================================" && \
    echo "INVESTIGACAO DETETIVE - DEPOIS DAS CORRECOES" && \
    echo "=============================================" && \
    echo "" && \
    echo "1. CONTEUDO DO config/app.php (linhas 444-447):" && \
    sed -n '444,447p' config/app.php && \
    echo "" && \
    echo "2. BUSCANDO TODAS AS REFERENCIAS A 'cipher' NO ARQUIVO:" && \
    grep -n "cipher" config/app.php && \
    echo "" && \
    echo "3. BUSCANDO TODAS AS REFERENCIAS A 'key' NO ARQUIVO:" && \
    grep -n "'key'" config/app.php && \
    echo "============================================="

RUN rm -rf bootstrap/cache/*.php storage/framework/cache/data/* storage/framework/views/*

COPY --from=build-assets /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html && chmod -R 775 storage bootstrap/cache

RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*

RUN echo 'server { listen 80; root /var/www/html/public; index index.php; location / { try_files $uri $uri/ /index.php?$query_string; } location ~ \.php$ { include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; fastcgi_pass 127.0.0.1:9000; } }' > /etc/nginx/conf.d/default.conf

RUN echo '#!/bin/sh' > /usr/local/bin/start.sh && \
    echo '' >> /usr/local/bin/start.sh && \
    echo 'echo "=================================================="' >> /usr/local/bin/start.sh && \
    echo 'echo "DELACAO PREMIADA - INVESTIGACAO EM RUNTIME"' >> /usr/local/bin/start.sh && \
    echo 'echo "=================================================="' >> /usr/local/bin/start.sh && \
    echo 'echo ""' >> /usr/local/bin/start.sh && \
    echo 'echo "1. VARIAVEIS DE AMBIENTE:"' >> /usr/local/bin/start.sh && \
    echo 'echo "   APP_ENV: ${APP_ENV}"' >> /usr/local/bin/start.sh && \
    echo 'echo "   APP_CIPHER: ${APP_CIPHER}"' >> /usr/local/bin/start.sh && \
    echo 'echo "   APP_KEY (primeiros 30 chars): ${APP_KEY:0:30}..."' >> /usr/local/bin/start.sh && \
    echo 'echo ""' >> /usr/local/bin/start.sh && \
    echo 'echo "2. TAMANHO DA APP_KEY DECODIFICADA:"' >> /usr/local/bin/start.sh && \
    echo 'APP_KEY_DECODED=$(echo "${APP_KEY#base64:}" | base64 -d 2>/dev/null | wc -c)' >> /usr/local/bin/start.sh && \
    echo 'echo "   Bytes: $APP_KEY_DECODED"' >> /usr/local/bin/start.sh && \
    echo 'if [ "$APP_KEY_DECODED" = "32" ]; then' >> /usr/local/bin/start.sh && \
    echo '    echo "   Status: OK para AES-256-CBC"' >> /usr/local/bin/start.sh && \
    echo 'elif [ "$APP_KEY_DECODED" = "16" ]; then' >> /usr/local/bin/start.sh && \
    echo '    echo "   Status: OK para AES-128-CBC"' >> /usr/local/bin/start.sh && \
    echo 'else' >> /usr/local/bin/start.sh && \
    echo '    echo "   Status: ERRO - Tamanho invalido!"' >> /usr/local/bin/start.sh && \
    echo 'fi' >> /usr/local/bin/start.sh && \
    echo 'echo ""' >> /usr/local/bin/start.sh && \
    echo 'echo "3. CONTEUDO REAL DO config/app.php (cipher):"' >> /usr/local/bin/start.sh && \
    echo 'grep -A 1 "cipher" config/app.php | head -2' >> /usr/local/bin/start.sh && \
    echo 'echo ""' >> /usr/local/bin/start.sh && \
    echo 'echo "4. TESTE DE CRIPTOGRAFIA:"' >> /usr/local/bin/start.sh && \
    echo 'php artisan tinker --execute="try { Crypt::encryptString(\"teste\"); echo \"Criptografia: OK\n\"; } catch (\Exception \$e) { echo \"Criptografia: ERRO - \" . \$e->getMessage() . \"\n\"; }"' >> /usr/local/bin/start.sh && \
    echo 'echo ""' >> /usr/local/bin/start.sh && \
    echo 'echo "5. CONFIGURACAO QUE O LARAVEL ESTA USANDO:"' >> /usr/local/bin/start.sh && \
    echo 'php artisan tinker --execute="echo \"   APP_KEY: \" . substr(config(\"app.key\"), 0, 30) . \"...\n\"; echo \"   APP_CIPHER: \" . config(\"app.cipher\") . \"\n\";"' >> /usr/local/bin/start.sh && \
    echo 'echo ""' >> /usr/local/bin/start.sh && \
    echo 'echo "=================================================="' >> /usr/local/bin/start.sh && \
    echo 'echo ""' >> /usr/local/bin/start.sh && \
    echo '' >> /usr/local/bin/start.sh && \
    echo 'sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf' >> /usr/local/bin/start.sh && \
    echo '' >> /usr/local/bin/start.sh && \
    echo 'php artisan config:clear' >> /usr/local/bin/start.sh && \
    echo '' >> /usr/local/bin/start.sh && \
    echo 'php artisan migrate --force > /dev/null 2>&1 || echo "DB OK"' >> /usr/local/bin/start.sh && \
    echo '' >> /usr/local/bin/start.sh && \
    echo 'php-fpm -D' >> /usr/local/bin/start.sh && \
    echo '' >> /usr/local/bin/start.sh && \
    echo 'nginx -g "daemon off;"' >> /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
