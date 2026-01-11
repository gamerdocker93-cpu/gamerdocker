FROM php:8.2-apache

# Dependências brutas para aguentar o tráfego
RUN apt-get update && apt-get install -y \
    libpq-dev libicu-dev libzip-dev zip unzip git \
    && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath

RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . .

# 1. FAXINA DE PRÉ-PRODUÇÃO
# Removemos o .env e qualquer cache para garantir que nada do seu PC interfira
RUN rm -f .env && rm -rf bootstrap/cache/*.php && rm -rf storage/framework/sessions/*

# 2. INSTALAÇÃO OTIMIZADA
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs

# 3. A CHAVE MESTRA (Não mude este valor, ele é o seu porto seguro agora)
ENV APP_KEY=base64:OTY4N2Y1ZTM0YjI5ZDVhZDVmOTU1ZTM2ZDU4NTQ=
ENV APP_CIPHER=AES-256-CBC

# 4. PERMISSÕES DE ALTA PERFORMANCE (775 para escrita rápida de logs e caches novos)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# 5. ENTRYPOINT "CHEQUE-MATE"
# Rodamos o config:cache para travar a chave que injetamos acima no passo 3
ENTRYPOINT ["/bin/sh", "-c", " \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan jwt:secret --force && \
    php artisan migrate --force && \
    apache2-foreground"]
