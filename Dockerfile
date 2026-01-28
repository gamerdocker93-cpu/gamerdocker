FROM node:18-alpine AS build-assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql intl zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# php-fpm via TCP
RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html

# Composer (sem depender de curl/wget)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# garante diretórios do Laravel (evita “Please provide a valid cache path.”)
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# melhor cache do composer (instala deps sem scripts para não precisar do artisan no build)
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

# copia o projeto
COPY . .

# agora que o projeto existe, pode rodar scripts
RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true

# assets do Vite
COPY --from=build-assets /app/public/build ./public/build

RUN rm -f bootstrap/cache/*.php && \
    rm -rf storage/framework/cache/data/* && \
    rm -rf storage/framework/views/*

RUN chown -R www-data:www-data /var/www/html && chmod -R 775 storage bootstrap/cache

RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*
RUN printf '%s\n' \
'server {' \
'  listen 80;' \
'  root /var/www/html/public;' \
'  index index.php;' \
'  location / { try_files $uri $uri/ /index.php?$query_string; }' \
'  location ~ \.php$ {' \
'    include fastcgi_params;' \
'    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' \
'    fastcgi_pass 127.0.0.1:9000;' \
'  }' \
'}' > /etc/nginx/conf.d/default.conf

# ---- CACHEBUST: mude no Railway (ex: 2,3,4) para forçar rebuild e evitar cache do start.sh
ARG CACHEBUST=1
RUN echo "CACHEBUST=$CACHEBUST"

RUN cat > /usr/local/bin/start.sh << 'SCRIPT_END'
#!/bin/bash
set -e

echo "=================================================="
echo "INICIANDO APLICACAO LARAVEL"
echo "=================================================="

# Ajusta porta Railway
sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf

echo ""
echo "VERIFICACAO:"
echo "  APP_CIPHER: ${APP_CIPHER}"
echo "  APP_KEY: ${APP_KEY}"
echo ""

if [ -z "${APP_KEY}" ]; then
  echo "ERRO: APP_KEY nao definido nas Variables da Railway."
  exit 1
fi

# Remove aspas + remove \r \n + remove espaços nas pontas (xargs)
APP_KEY_CLEAN=$(printf "%s" "$APP_KEY" \
  | sed 's/^"//; s/"$//; s/^\x27//; s/\x27$//' \
  | tr -d '\r\n' \
  | xargs)

if printf "%s" "$APP_KEY_CLEAN" | grep -q '^base64:'; then
  KEY_B64="${APP_KEY_CLEAN#base64:}"
  KEY_LEN=$(php -r '$k=base64_decode($argv[1], true); echo ($k===false) ? 0 : strlen($k);' "$KEY_B64")
else
  KEY_LEN=$(php -r 'echo strlen($argv[1]);' "$APP_KEY_CLEAN")
fi
export APP_KEY="$APP_KEY_CLEAN"
export APP_CIPHER="${APP_CIPHER:-aes-256-cbc}"
echo "  APP_KEY bytes: ${KEY_LEN}"
if [ "${APP_CIPHER}" = "aes-256-cbc" ] && [ "${KEY_LEN}" != "32" ]; then
  echo "ERRO: APP_KEY invalido. Para aes-256-cbc precisa 32 bytes (base64: + 32 bytes)."
  exit 1
fi

# limpa caches
php artisan config:clear >/dev/null 2>&1 || true
php artisan cache:clear  >/dev/null 2>&1 || true
php artisan view:clear   >/dev/null 2>&1 || true

# =========================
# Migrations (sem loop)
# =========================
if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
  echo "INFO Running migrations..."
  set +e
  OUT=$(php artisan migrate --force --no-interaction 2>&1)
  CODE=$?
  set -e

  if [ $CODE -ne 0 ]; then
    echo "$OUT"
    echo "AVISO: migrations falharam, mas o container vai continuar para evitar restart loop."
    # NÃO derruba o container
  else
    echo "DB: migrations OK"
  fi
else
  echo "INFO RUN_MIGRATIONS!=1 -> pulando migrations."
fi

echo "=================================================="
echo "APLICACAO PRONTA"
echo "=================================================="

php-fpm -D
nginx -g "daemon off;"
SCRIPT_END

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]