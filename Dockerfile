FROM php:8.2-apache

# 1. Instala dependências do sistema e extensões PHP
RUN apt-get update && apt-get install -y \
    libpq-dev libicu-dev libzip-dev zip unzip git postgresql-client libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql pgsql intl zip bcmath gd

# 2. SOLUÇÃO DEFINITIVA PARA O ERRO MPM
# Removemos os links simbólicos de outros MPMs e forçamos apenas o mpm_prefork
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_worker.load \
    && ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf \
    && a2enmod rewrite

WORKDIR /var/www/html

# 3. Copia os arquivos do projeto
COPY . .

# 4. Configura o DocumentRoot para /public do Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# 5. Instala o Composer e as dependências
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# 6. Ajusta permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Script de Inicialização (Garante a porta e limpa PIDs antigos)
RUN echo '#!/bin/sh\n\
sed -i "s/Listen .*/Listen ${PORT}/g" /etc/apache2/ports.conf\n\
sed -i "s/<VirtualHost \*:.*/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf\n\
rm -f /var/run/apache2/apache2.pid\n\
exec apache2-foreground' > /usr/local/bin/start-app.sh \
    && chmod +x /usr/local/bin/start-app.sh

EXPOSE ${PORT}

CMD ["/usr/local/bin/start-app.sh"]
