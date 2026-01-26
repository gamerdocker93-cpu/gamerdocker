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
# 2) PHP + Nginx (produção)
# =========================
FROM php:8.2-fpm

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp

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

# php-fpm via TCP (127.0.0.1:9000)
RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html

# Composer (instalação confiável)
RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php \
    && php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm -f /tmp/composer-setup.php

# Copia apenas composer.* para aproveitar cache
COPY composer.json composer.lock ./

# Cria diretórios essenciais antes do composer
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Instala dependências SEM scripts (evita chamar artisan no build)
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# Copia o projeto todo
COPY . .

# Copia assets do Vite
COPY --from=build-assets /app/public/build ./public/build

# Limpa caches e ajusta permissões
RUN rm -f bootstrap/cache/*.php || true \
    && rm -rf storage/framework/cache/data/* storage/framework/views/* || true \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Nginx conf
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

# Start script
RUN cat > /usr/local/bin/start.sh << 'SCRIPT_END'
#!/bin/bash
set -e

echo "=================================================="
echo "INICIANDO APLICACAO LARAVEL"
echo "=================================================="

# Ajusta porta (Railway usa PORT)
sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf

# Garantir diretórios
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chmod -R 775 storage bootstrap/cache || true

echo ""
echo "VERIFICACAO:"
echo "  APP_CIPHER: ${APP_CIPHER}"
echo "  APP_KEY: ${APP_KEY}"
echo ""

if [ -z "${APP_KEY}" ]; then
  echo "ERRO: APP_KEY nao definido nas Variables da Railway."
  exit 1
fi

# remove aspas acidentais
APP_KEY_CLEAN=$(echo -n "${APP_KEY}" | sed 's/^"//; s/"$//; s/^\x27//; s/\x27$//')

# mede bytes corretamente
if echo "${APP_KEY_CLEAN}" | grep -q '^base64:'; then
  KEY_B64="${APP_KEY_CLEAN#base64:}"
  KEY_LEN=$(K="$KEY_B64" php -r '$k=base64_decode(getenv("K"), true); echo $k===false ? -1 : strlen($k);')
else
  KEY_LEN=$(K="$APP_KEY_CLEAN" php -r 'echo strlen(getenv("K"));')
fi

echo "  APP_KEY bytes: ${KEY_LEN}"

# valida para aes-256-cbc (32 bytes)
if [ "${APP_CIPHER}" = "aes-256-cbc" ] && [ "${KEY_LEN}" != "32" ]; then
  echo "ERRO: APP_KEY invalido. Para aes-256-cbc precisa 32 bytes (base64: + 32 bytes)."
  exit 1
fi

# roda scripts do Laravel agora (runtime) - seguro
php artisan package:discover --ansi || true

# limpa caches
php artisan config:clear >/dev/null 2>&1 || true
php artisan cache:clear  >/dev/null 2>&1 || true
php artisan view:clear   >/dev/null 2>&1 || true

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
  # ignora "tabela já existe"
  echo "$OUT" | grep -q "Base table or view already exists" && echo "DB: tabela ja existe (ok)" || exit $CODE
else
  echo "DB: migrations OK"
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