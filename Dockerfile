# =========================
# 1) Build dos assets (Vite)
# =========================
FROM node:18-alpine AS build-assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build


# =========================
# 2) App PHP + Nginx
# =========================
FROM php:8.2-fpm

# Dependências do sistema + extensões PHP
RUN apt-get update && apt-get install -y \
    nginx \
    libicu-dev \
    libzip-dev \
    zip unzip git curl \
    libpng-dev libjpeg-dev libfreetype6-dev \
    bash \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql intl zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# php-fpm via TCP (Railway/nginx conversam via 127.0.0.1:9000)
RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html

# Instalar Composer (sem depender da imagem composer:2)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && php -r "unlink('composer-setup.php');"

# Copia o projeto primeiro (precisa existir artisan/bootstrap/config p/ scripts do Composer)
COPY . .

# Cria pastas necessárias (evita "Please provide a valid cache path")
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Instala dependências PHP (Composer)
# Se algum script do Laravel falhar aqui, não derruba o build (porque ainda não tem .env/APP_KEY no build)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
 || (echo "WARN: composer scripts falharam no build (ok). Continuando..." && true)

# Coloca os assets compilados no lugar
COPY --from=build-assets /app/public/build ./public/build

# Config do Nginx (limpa configs padrão e cria uma básica)
RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/* \
 && printf '%s\n' \
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


# =========================
# 3) Start script
# =========================
RUN cat > /usr/local/bin/start.sh << 'SCRIPT_END'
#!/bin/bash
set -e

echo "=================================================="
echo "INICIANDO APLICACAO LARAVEL"
echo "=================================================="

# Ajusta porta Railway
sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf

# Garante permissões/pastas (em runtime também)
mkdir -p \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/framework/cache/data \
  bootstrap/cache || true

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# Limpa caches (sem derrubar)
php artisan config:clear >/dev/null 2>&1 || true
php artisan cache:clear  >/dev/null 2>&1 || true
php artisan view:clear   >/dev/null 2>&1 || true

echo ""
echo "VERIFICACAO:"
echo "  APP_CIPHER: ${APP_CIPHER}"
echo "  APP_KEY: ${APP_KEY}"
echo ""

if [ -z "${APP_KEY}" ]; then
  echo "ERRO: APP_KEY nao definido nas Variables da Railway."
  exit 1
fi

# remove aspas acidentais (muito comum em variável)
APP_KEY_CLEAN=$(echo -n "${APP_KEY}" | sed 's/^"//; s/"$//; s/^\x27//; s/\x27$//')

# ======= CORREÇÃO AQUI (APP_KEY bytes: 0) =======
if echo "${APP_KEY_CLEAN}" | grep -q '^base64:'; then
  KEY_B64="${APP_KEY_CLEAN#base64:}"
  KEY_LEN=$(php -r '$k=base64_decode($argv[1], true); echo $k===false ? -1 : strlen($k);' "$KEY_B64")
else
  KEY_LEN=$(php -r 'echo strlen($argv[1]);' "$APP_KEY_CLEAN")
fi
# ===============================================

echo "  APP_KEY bytes: ${KEY_LEN}"
if [ "${KEY_LEN}" != "32" ] && [ "${APP_CIPHER}" = "aes-256-cbc" ]; then
  echo "ERRO: APP_KEY invalido. Para aes-256-cbc precisa 32 bytes (base64: + 32 bytes)."
  exit 1
fi

echo ""
echo "TESTE DE CRIPTOGRAFIA:"
php -r '
require "vendor/autoload.php";
$app=require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
  $e=encrypt("teste123");
  $d=decrypt($e);
  echo $d==="teste123" ? "RESULTADO: OK\n" : "RESULTADO: ERRO\n";
} catch (Exception $ex) {
  echo "RESULTADO: ERRO - ".$ex->getMessage()."\n";
}
'
echo ""

echo "INFO Running migrations..."
set +e
OUT=$(php artisan migrate --force --no-interaction 2>&1)
CODE=$?
set -e

if [ $CODE -ne 0 ]; then
  echo "$OUT"
  echo "$OUT" | grep -q "Base table or view already exists" && echo "DB: tabela ja existe (ok)" || exit $CODE
else
  echo "DB: migrations OK"
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