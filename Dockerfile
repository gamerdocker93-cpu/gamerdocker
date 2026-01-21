# Dockerfile Otimizado para Laravel 10 + Vue 3 no Railway
# Análise e correção por: Dev Sênior Especialista em Railway

# ===================================================================
# STAGE 1: Build dos Assets (Vue.js / Vite)
# ===================================================================
FROM node:18-alpine AS build-assets

WORKDIR /app

# Copia apenas os arquivos de dependência para aproveitar o cache do Docker
COPY package.json yarn.lock ./ 

# Instala dependências
RUN yarn install

# Copia o restante dos arquivos e compila os assets
COPY . .
RUN yarn build

# ===================================================================
# STAGE 2: Imagem Final da Aplicação PHP
# ===================================================================
FROM php:8.2-fpm

# Instala dependências do sistema (Nginx, extensões PHP, etc.)
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

# Configura PHP-FPM para se comunicar com Nginx
RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html

# Instala o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia apenas os arquivos do Composer para cachear as dependências
COPY database/ database/
COPY composer.json composer.lock ./

# Instala dependências do Composer
RUN composer install --no-dev --no-scripts --ignore-platform-reqs --no-autoloader

# Copia o restante do código da aplicação
COPY . .

# Gera o autoloader do Composer DEPOIS de copiar todos os arquivos
RUN composer dump-autoload --optimize

# ===================================================================
# CORREÇÃO DAS CIFRAS (O PONTO CRÍTICO)
# ===================================================================

# 1. Busca por AES-128-CBC para diagnóstico (opcional, mas bom para logs)
RUN echo "--- BUSCANDO ARQUIVOS COM AES-128-CBC (ANTES DA CORREÇÃO) ---" && \
    grep -r "AES-128-CBC" . || echo "Nenhum arquivo encontrado com AES-128-CBC"

# 2. Substitui TODAS as ocorrências de AES-128-CBC por AES-256-CBC
#    Isso agora inclui a pasta /vendor, corrigindo pacotes de terceiros
RUN find . -type f -name "*.php" -exec sed -i 's/AES-128-CBC/AES-256-CBC/g' {} +

# 3. Garante que a configuração de cifra no config/app.php use a variável de ambiente
#    Isso é MUITO IMPORTANTE para que o Laravel leia a variável do Railway
RUN sed -i "s/'cipher' => 'AES-128-CBC'/'cipher' => env('APP_CIPHER', 'AES-256-CBC')/g" config/app.php

# 4. ❌ REMOVIDO O HARDCODING DA APP_KEY ❌
#    A linha abaixo foi removida para que o Laravel use a variável de ambiente do Railway
#    RUN sed -i "s/'key' => .*/'key' => 'base64:SUA_CHAVE_HARDCODED',/g" config/app.php

# ===================================================================
# FINALIZAÇÃO DO BUILD
# ===================================================================

# Limpa o cache de configuração do Laravel APÓS todas as modificações
RUN php artisan config:clear && php artisan view:clear

# Copia os assets compilados do primeiro estágio
COPY --from=build-assets /app/public/build ./public/build

# Ajusta permissões para o Nginx/PHP-FPM
RUN chown -R www-data:www-data /var/www/html && chmod -R 775 storage bootstrap/cache

# Configura o Nginx
RUN rm -rf /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*
RUN echo 'server { listen 80; root /var/www/html/public; index index.php; location / { try_files $uri $uri/ /index.php?$query_string; } location ~ \.php$ { include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; fastcgi_pass 127.0.0.1:9000; } }' > /etc/nginx/conf.d/default.conf

# ===================================================================
# SCRIPT DE INICIALIZAÇÃO (start.sh)
# ===================================================================
RUN echo '#!/bin/sh' > /usr/local/bin/start.sh

# Adiciona um log para verificar as variáveis de ambiente no runtime
RUN echo 'echo "--- VERIFICANDO VARIÁVEIS DE AMBIENTE NO RAILWAY ---"' >> /usr/local/bin/start.sh
RUN echo 'echo "APP_ENV: ${APP_ENV}"' >> /usr/local/bin/start.sh
RUN echo 'echo "APP_CIPHER: ${APP_CIPHER}"' >> /usr/local/bin/start.sh
RUN echo 'echo "-----------------------------------------------------"' >> /usr/local/bin/start.sh

# Configura o Nginx para usar a porta do Railway
RUN echo 'sed -i "s/listen 80;/listen ${PORT:-8080};/g" /etc/nginx/conf.d/default.conf' >> /usr/local/bin/start.sh

# Limpa o cache novamente no runtime (garantia extra)
RUN echo 'php artisan config:clear' >> /usr/local/bin/start.sh

# Executa as migrações do banco de dados
RUN echo 'php artisan migrate --force' >> /usr/local/bin/start.sh

# Inicia os serviços
RUN echo 'php-fpm -D' >> /usr/local/bin/start.sh
RUN echo 'nginx -g "daemon off;"' >> /usr/local/bin/start.sh

# Torna o script executável
RUN chmod +x /usr/local/bin/start.sh

# Expõe a porta e define o comando de inicialização
EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]