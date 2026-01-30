FROM node:18-alpine AS build-assets
WORKDIR /app

# Copia o projeto inteiro ANTES de buildar (evita manifest antigo)
COPY . .

# Dependências e build
RUN npm install
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
# ==========================================
RUN sed -i 's/;clear_env = no/clear_env = no/g; s/clear_env = yes/clear_env = no/g' /usr/local/etc/php-fpm.d/www.conf

# php-fpm via TCP
RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

COPY composer.json composer.lock ./

# INJECAO: garantir que o arquivo artisan exista antes de qualquer script do composer
COPY artisan ./artisan

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

COPY . .

RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true

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
'  location / { try_files $uri $uri/ /index.php?$query_string; }' \
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
echo "INICIANDO APLICACAO LARAVEL"
echo "=================================================="

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

# Evita sobrescrita por .env (se existir)
rm -f /var/www/html/.env 2>/dev/null || true

# Garante modo PROD (remove hot files)
rm -f /var/www/html/public/hot 2>/dev/null || true
rm -f /var/www/html/public/build/hot 2>/dev/null || true

echo ""
echo "================ DIAG RUNTIME ================"
echo "SHELL APP_KEY (curto): $(printf '%s' "$APP_KEY" | cut -c1-25)..."
echo "SHELL APP_CIPHER: $APP_CIPHER"
echo "APP_KEY bytes (calculado): ${KEY_LEN}"

echo ""
echo "1) PHP: getenv / \$_ENV / \$_SERVER"
php -r '
echo "getenv(APP_KEY)=".(getenv("APP_KEY")?: "NULL").PHP_EOL;
echo "getenv(APP_CIPHER)=".(getenv("APP_CIPHER")?: "NULL").PHP_EOL;
echo "_ENV[APP_KEY]=".(isset($_ENV["APP_KEY"])?$_ENV["APP_KEY"]:"NULL").PHP_EOL;
echo "_SERVER[APP_KEY]=".(isset($_SERVER["APP_KEY"])?$_SERVER["APP_KEY"]:"NULL").PHP_EOL;
echo "_ENV[APP_CIPHER]=".(isset($_ENV["APP_CIPHER"])?$_ENV["APP_CIPHER"]:"NULL").PHP_EOL;
echo "_SERVER[APP_CIPHER]=".(isset($_SERVER["APP_CIPHER"])?$_SERVER["APP_CIPHER"]:"NULL").PHP_EOL;
'

echo ""
echo "2) Laravel bootstrap: env(APP_KEY) / config(app.key/app.cipher)"
php -r '
require "vendor/autoload.php";
$app=require "bootstrap/app.php";
$k=$app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();
echo "env(APP_KEY)=".env("APP_KEY").PHP_EOL;
echo "config(app.key)=".config("app.key").PHP_EOL;
echo "config(app.cipher)=".config("app.cipher").PHP_EOL;
' || true

echo ""
echo "3) .env no container?"
ls -la /var/www/html/.env* 2>/dev/null || echo "Nenhum .env encontrado"

echo ""
echo "4) bootstrap/cache"
ls -la /var/www/html/bootstrap/cache 2>/dev/null || true
ls -la /var/www/html/bootstrap/cache/config.php 2>/dev/null || echo "config.php nao existe"

echo ""
echo "5) php-fpm loaded conf / clear_env"
php-fpm -tt 2>&1 | grep -i -n "loaded configuration\|include\|pool\|clear_env" || true

echo ""
if [ "${RUN_DIAG_KEYSCAN:-0}" = "1" ]; then
  echo "6) Procurando a chave antiga (9687f / OTY4N2Y1) e overrides de app.key"

  grep -R --line-number \
    --exclude=Dockerfile \
    --exclude-dir=vendor \
    --exclude-dir=node_modules \
    "9687f5e" /var/www/html 2>/dev/null | head -n 80 || true

  grep -R --line-number \
    --exclude=Dockerfile \
    --exclude-dir=vendor \
    --exclude-dir=node_modules \
    "OTY4N2Y1" /var/www/html 2>/dev/null | head -n 80 || true

  grep -R --line-number \
    --exclude-dir=vendor \
    --exclude-dir=node_modules \
    "app\.key" /var/www/html/app /var/www/html/config 2>/dev/null | head -n 120 || true

  grep -R --line-number \
    --exclude-dir=vendor \
    --exclude-dir=node_modules \
    "config\s*\(\s*\[\s*['\"]app\.key['\"]" /var/www/html/app /var/www/html/config 2>/dev/null | head -n 120 || true
else
  echo "6) Keyscan desativado (defina RUN_DIAG_KEYSCAN=1 para ativar)."
fi

echo "================ FIM DIAG ===================="
echo ""

if [ "${APP_CIPHER}" = "aes-256-cbc" ] && [ "${KEY_LEN}" != "32" ]; then
  echo "ERRO: APP_KEY invalido (precisa 32 bytes)."
  exit 1
fi

# Limpa caches runtime (FORCADO)
rm -f bootstrap/cache/*.php 2>/dev/null || true
php artisan optimize:clear >/dev/null 2>&1 || true
php artisan view:clear     >/dev/null 2>&1 || true
php artisan config:clear   >/dev/null 2>&1 || true
php artisan cache:clear    >/dev/null 2>&1 || true
php artisan route:clear    >/dev/null 2>&1 || true

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