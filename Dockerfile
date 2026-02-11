FROM node:18-alpine AS build-assets
WORKDIR /app

# Copia o projeto inteiro ANTES de buildar (evita manifest antigo)
COPY . .

# Dependências e build
RUN npm install
RUN rm -rf public/build
RUN npm run build


FROM php:8.2-fpm

# Evita prompts e melhora estabilidade do apt no CI (Railway)
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
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

COPY . .

RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true

# Limpa build antigo antes de copiar o novo (evita misturar arquivos)
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
'  # Assets do Vite (public/build) - só arquivo, nada de pasta' \
'  location ^~ /build/ {' \
'    try_files $uri =404;' \
'    access_log off;' \
'    expires 1y;' \
'    add_header Cache-Control "public, max-age=31536000, immutable";' \
'  }' \
'' \
'  # IMPORTANTE: nao usar $uri/ aqui (evita 403 ao cair em diretorio tipo /build/)' \
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

sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf

# NÃO LOGAR SEGREDOS
echo ""
echo "VERIFICACAO:"
echo "  APP_ENV: ${APP_ENV}"
echo "  APP_DEBUG: ${APP_DEBUG}"
echo "  APP_CIPHER: ${APP_CIPHER:-aes-256-cbc}"
echo "  APP_KEY: (set? $( [ -n "${APP_KEY}" ] && echo yes || echo no ))"
echo "  DB_HOST: ${DB_HOST}"
echo "  DB_DATABASE: ${DB_DATABASE}"
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

# ============================================================
# INJECAO: GARANTIR QUE O BLADE PUXA VITE E CSRF (PROD)
# ============================================================
BLADE_FILE="/var/www/html/resources/views/layouts/app.blade.php"
if [ -f "$BLADE_FILE" ]; then
  if ! grep -q 'name="csrf-token"' "$BLADE_FILE"; then
    sed -i '/<\/head>/i\    <meta name="csrf-token" content="{{ csrf_token() }}">' "$BLADE_FILE" || true
  fi

  if ! grep -q "@vite(" "$BLADE_FILE"; then
    sed -i "/<\/head>/i\    @vite(['resources/css/app.css', 'resources/js/app.js'])" "$BLADE_FILE" || true
  fi
fi

echo ""
echo "================ VITE CHECK (public/build) ================"
if [ -f /var/www/html/public/build/manifest.json ]; then
  echo "manifest.json OK (size: $(wc -c < /var/www/html/public/build/manifest.json) bytes)"
else
  echo "ERRO: public/build/manifest.json nao existe"
fi

echo ""
echo "Assets em public/build/assets:"
ls -la /var/www/html/public/build/assets 2>/dev/null || echo "Sem pasta assets em public/build"

echo ""
echo "================ DIAG RUNTIME ================"
echo "APP_KEY bytes (calculado): ${KEY_LEN}"
echo "APP_CIPHER: ${APP_CIPHER}"

if [ "${APP_CIPHER}" = "aes-256-cbc" ] && [ "${KEY_LEN}" != "32" ]; then
  echo "ERRO: APP_KEY invalido (precisa 32 bytes)."
  exit 1
fi

# ============================================================
# CRITICO: NAO RODAR 'php artisan ...' AQUI
# (quando DB/Railway instavel, pode travar antes do BOOT)
# Limpa caches manualmente (SEM artisan)
# ============================================================
rm -f bootstrap/cache/*.php 2>/dev/null || true
rm -rf storage/framework/cache/* 2>/dev/null || true
rm -rf storage/framework/views/* 2>/dev/null || true

echo ""
echo "================ BOOT COMMANDS (obrigatorios) ================"

# Se você precisa rodar comandos obrigatórios, faça no Start Command do Railway,
# não aqui, para não travar o container.

echo "=================================================="
echo "APLICACAO PRONTA"
echo "=================================================="

php-fpm -D
nginx -g "daemon off;"
SCRIPT_END

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]