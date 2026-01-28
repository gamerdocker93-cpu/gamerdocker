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

# ==========================================
# FORÇA PHP-FPM A NÃO LIMPAR VARIÁVEIS ENV
# (RESOLVE BUG DO APP_KEY)
# ==========================================
RUN sed -i 's/;clear_env = no/clear_env = no/g; s/clear_env = yes/clear_env = no/g' /usr/local/etc/php-fpm.d/www.conf

# php-fpm via TCP
RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# garante diretórios do Laravel
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# deps PHP
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

# copia projeto
COPY . .

# autoload
RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true

# assets Vite
COPY --from=build-assets /app/public/build ./public/build

# limpa caches build
RUN rm -f bootstrap/cache/*.php && \
    rm -rf storage/framework/cache/data/* && \
    rm -rf storage/framework/views/*

RUN chown -R www-data:www-data /var/www/html && chmod -R 775 storage bootstrap/cache

# nginx
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

# cachebust
ARG CACHEBUST=1
RUN echo "CACHEBUST=$CACHEBUST"

# ==========================================
# START.SH
# ==========================================
RUN cat > /usr/local/bin/start.sh << 'SCRIPT_END'
#!/bin/bash
set -e

echo "=================================================="
echo "INICIANDO APLICACAO LARAVEL"
echo "=================================================="

# Porta Railway
sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf

echo ""
echo "VERIFICACAO:"
echo "  APP_CIPHER: ${APP_CIPHER}"
echo "  APP_KEY: ${APP_KEY}"
echo ""

if [ -z "${APP_KEY}" ]; then
  echo "ERRO: APP_KEY nao definido."
  exit 1
fi

# Limpa aspas e lixo
APP_KEY_CLEAN=$(printf "%s" "$APP_KEY" \
  | sed 's/^"//; s/"$//; s/^\x27//; s/\x27$//' \
  | tr -d '\r\n' \
  | xargs)

# Calcula bytes
if printf "%s" "$APP_KEY_CLEAN" | grep -q '^base64:'; then
  KEY_B64="${APP_KEY_CLEAN#base64:}"
  KEY_LEN=$(php -r '$k=base64_decode($argv[1], true); echo ($k===false)?0:strlen($k);' "$KEY_B64")
else
  KEY_LEN=$(php -r 'echo strlen($argv[1]);' "$APP_KEY_CLEAN")
fi

# EXPORTA PARA O PHP
export APP_KEY="$APP_KEY_CLEAN"
export APP_CIPHER="${APP_CIPHER:-aes-256-cbc}"

# ==========================================
# DIAGNOSTICO (ENV x LARAVEL x FPM)
# ==========================================
echo ""
echo "================ DIAG RUNTIME ================"
echo "SHELL APP_KEY (curto): $(printf '%s' "$APP_KEY" | cut -c1-25)..."
echo "SHELL APP_CIPHER: $APP_CIPHER"

echo ""
echo "1) PHP getenv()"
php -r 'echo "getenv(APP_ENV)=".getenv("APP_ENV").PHP_EOL;
echo "getenv(APP_KEY)=".getenv("APP_KEY").PHP_EOL;
echo "getenv(APP_CIPHER)=".getenv("APP_CIPHER").PHP_EOL;'

echo ""
echo "2) Laravel bootstrap -> config(app.key/app.cipher)"
php -r 'require "vendor/autoload.php";
$app=require "bootstrap/app.php";
$k=$app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();
echo "config(app.key)=".config("app.key").PHP_EOL;
echo "config(app.cipher)=".config("app.cipher").PHP_EOL;' || true

echo ""
echo "3) .env no container?"
ls -la /var/www/html/.env* 2>/dev/null || echo "Nenhum .env encontrado"
grep -n "APP_KEY\|APP_CIPHER\|APP_ENV" /var/www/html/.env 2>/dev/null || true

echo ""
echo "4) bootstrap/cache"
ls -la /var/www/html/bootstrap/cache 2>/dev/null || true
ls -la /var/www/html/bootstrap/cache/config.php 2>/dev/null || echo "config.php nao existe"

echo ""
echo "5) php-fpm loaded conf / clear_env"
php-fpm -tt 2>&1 | grep -i -n "loaded configuration\|include\|pool\|clear_env" || true

echo ""
echo "6) CHECANDO config/app.php (key/cipher reais do arquivo)"
php -r '$c=require "config/app.php"; echo "config/app.php key=".$c["key"].PHP_EOL; echo "config/app.php cipher=".$c["cipher"].PHP_EOL;'
grep -n "['\"]key['\"]\s*=>" /var/www/html/config/app.php || true
grep -n "APP_KEY" /var/www/html/config/app.php || true

echo ""
echo "7) PROCURANDO OVERRIDE de app.key (app/ e config/)"
grep -R --line-number "app\.key" /var/www/html/app /var/www/html/config 2>/dev/null | head -n 120 || true
grep -R --line-number "config\s*\(\s*\[\s*['\"]app\.key['\"]" /var/www/html/app /var/www/html/config 2>/dev/null | head -n 120 || true

echo "================ FIM DIAG ===================="
echo ""

# ==========================================
# REMOVE .ENV INTERNO (EVITA SOBRESCRITA)
# ==========================================
rm -f /var/www/html/.env 2>/dev/null || true

echo "  APP_KEY bytes: ${KEY_LEN}"

if [ "${APP_CIPHER}" = "aes-256-cbc" ] && [ "${KEY_LEN}" != "32" ]; then
  echo "ERRO: APP_KEY invalido (precisa 32 bytes)."
  exit 1
fi

# Limpa caches runtime
rm -f bootstrap/cache/*.php 2>/dev/null || true

php artisan config:clear >/dev/null 2>&1 || true
php artisan cache:clear  >/dev/null 2>&1 || true
php artisan view:clear   >/dev/null 2>&1 || true

# =========================
# MIGRATIONS
# =========================
if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
  echo "INFO Running migrations..."
  set +e
  OUT=$(php artisan migrate --force --no-interaction 2>&1)
  CODE=$?
  set -e

  if [ $CODE -ne 0 ]; then
    echo "$OUT"
    echo "AVISO: migrations falharam."
  else
    echo "DB: migrations OK"
  fi
else
  echo "INFO RUN_MIGRATIONS!=1"
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