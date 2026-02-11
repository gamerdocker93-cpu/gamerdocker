FROM node:18-alpine AS build-assets
WORKDIR /app

COPY . .

RUN npm install
RUN rm -rf public/build
RUN npm run build


FROM php:8.2-fpm

ARG DEBIAN_FRONTEND=noninteractive

RUN set -eux; \
    apt-get -o Acquire::Retries=5 update; \
    apt-get install -y --no-install-recommends \
      nginx \
      libicu-dev \
      libzip-dev \
      zip unzip git \
      libpng-dev libjpeg-dev libfreetype6-dev \
      bash \
      ca-certificates \
    ; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install pdo_mysql intl zip bcmath gd; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*

# FORÇA PHP-FPM A NÃO LIMPAR VARIÁVEIS ENV
RUN sed -i 's/;clear_env = no/clear_env = no/g; s/clear_env = yes/clear_env = no/g' /usr/local/etc/php-fpm.d/www.conf

# php-fpm via TCP
RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

COPY . .

RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true

# Limpa build antigo antes de copiar o novo
RUN rm -rf /var/www/html/public/build

# Copia os assets buildados do estágio do Node
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
'' \
'  location ^~ /build/ {' \
'    try_files $uri =404;' \
'    access_log off;' \
'    expires 1y;' \
'    add_header Cache-Control "public, max-age=31536000, immutable";' \
'  }' \
'' \
'  location / {' \
'    try_files $uri /index.php?$query_string;' \
'  }' \
'' \
'  location ~ \.php$ {' \
'    include fastcgi_params;' \
'    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' \
'    fastcgi_pass 127.0.0.1:9000;' \
'  }' \
'}' > /etc/nginx/conf.d/default.conf

ARG CACHEBUST=1
RUN echo "CACHEBUST=$CACHEBUST"

RUN cat > /usr/local/bin/start.sh << 'SCRIPT_END'
#!/bin/bash
set -e

echo "=================================================="
echo "INICIANDO APLICACAO LARAVEL (NGINX + PHP-FPM)"
echo "=================================================="

# Ajusta porta do Railway
sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf

# Não deixar .env do repo sobrescrever env do Railway
rm -f /var/www/html/.env 2>/dev/null || true

# Remove hot do Vite
rm -f /var/www/html/public/hot 2>/dev/null || true
rm -f /var/www/html/public/build/hot 2>/dev/null || true

echo ""
echo "VERIFICACAO:"
echo "  APP_ENV: ${APP_ENV:-production}"
echo "  APP_DEBUG: ${APP_DEBUG:-false}"
echo "  APP_CIPHER: ${APP_CIPHER:-aes-256-cbc}"
echo "  APP_KEY: (set? $( [ -n "${APP_KEY}" ] && echo yes || echo no ))"
echo "  DB_HOST: ${DB_HOST:-}"
echo "  DB_DATABASE: ${DB_DATABASE:-}"
echo ""

if [ -z "${APP_KEY}" ]; then
  echo "ERRO: APP_KEY nao definido."
  exit 1
fi

APP_KEY_CLEAN=$(printf "%s" "$APP_KEY" \
  | sed 's/^"//; s/"$//; s/^\x27//; s/\x27$//' \
  | tr -d '\r\n' \
  | xargs)

if printf "%s" "$APP_KEY_CLEAN" | grep -q '^base64:'; then
  KEY_B64="${APP_KEY_CLEAN#base64:}"
  KEY_LEN=$(php -r '$k=base64_decode($argv[1], true); echo ($k===false)?0:strlen($k);' "$KEY_B64")
else
  KEY_LEN=$(php -r 'echo strlen($argv[1]);' "$APP_KEY_CLEAN")
fi

export APP_KEY="$APP_KEY_CLEAN"
export APP_CIPHER="${APP_CIPHER:-aes-256-cbc}"

echo ""
echo "================ VITE CHECK (public/build) ================"
if [ -f /var/www/html/public/build/manifest.json ]; then
  echo "manifest.json OK (size: $(wc -c < /var/www/html/public/build/manifest.json) bytes)"
else
  echo "AVISO: public/build/manifest.json nao existe"
fi

echo ""
echo "================ DIAG RUNTIME ================"
echo "APP_KEY bytes (calculado): ${KEY_LEN}"
echo "APP_CIPHER: ${APP_CIPHER}"

if [ "${APP_CIPHER}" = "aes-256-cbc" ] && [ "${KEY_LEN}" != "32" ]; then
  echo "ERRO: APP_KEY invalido (precisa 32 bytes)."
  exit 1
fi

# Limpa caches runtime (rápido; não pode travar)
rm -f bootstrap/cache/*.php 2>/dev/null || true
php artisan optimize:clear >/dev/null 2>&1 || true

# ----------------------------
# DB CHECK (nao pode travar boot)
# ----------------------------
DB_OK=0
if [ -n "${DB_HOST:-}" ] && [ -n "${DB_DATABASE:-}" ] && [ -n "${DB_USERNAME:-}" ]; then
  php -r '
    $h=getenv("DB_HOST"); $db=getenv("DB_DATABASE"); $u=getenv("DB_USERNAME"); $p=getenv("DB_PASSWORD");
    $port=getenv("DB_PORT") ?: "3306";
    try {
      $pdo=new PDO("mysql:host={$h};port={$port};dbname={$db};charset=utf8mb4",$u,$p,[
        PDO::ATTR_TIMEOUT=>3,
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION
      ]);
      $pdo->query("SELECT 1");
      exit(0);
    } catch(Throwable $e) { exit(1); }
  ' && DB_OK=1 || DB_OK=0
fi

if [ "$DB_OK" = "1" ]; then
  echo "DB: OK (conexao rapida)"

  echo ""
  echo "=========== BOOT COMMANDS (nao podem derrubar) ==========="
  # tudo com timeout para nunca travar o container
  timeout 20s php artisan tempadmin:create >/dev/null 2>&1 || true
  timeout 20s php artisan fix:admin-role   >/dev/null 2>&1 || true
  timeout 25s php artisan spin:init        >/dev/null 2>&1 || true

  # migrations opcionais (só se alguém ligar)
  if [ "${RUN_MIGRATIONS:-0}" = "1" ]; then
    echo "INFO Running migrations..."
    timeout 60s php artisan migrate --force --no-interaction >/dev/null 2>&1 || true
  else
    echo "INFO RUN_MIGRATIONS!=1 (skip)"
  fi
else
  echo "AVISO: DB nao respondeu rapido (timeout). Vou subir o servidor mesmo assim."
  echo "       (Isso evita 504/queda do container. Quando o DB estabilizar, reinicia o deploy.)"
fi

echo "=================================================="
echo "APLICACAO PRONTA"
echo "=================================================="

php-fpm -D
exec nginx -g "daemon off;"
SCRIPT_END

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]