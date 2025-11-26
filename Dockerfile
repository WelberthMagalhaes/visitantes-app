FROM php:8.4-apache

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Habilita mod_rewrite
RUN a2enmod rewrite

# Instala extensões necessárias
RUN docker-php-ext-install pdo pdo_sqlite pdo_pgsql

# Copia arquivos do projeto
COPY . /var/www/html/

# Garante que o .env seja copiado
COPY .env /var/www/html/.env

# Define DocumentRoot para pasta public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permite .htaccess override
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Permissões para SQLite
RUN chown -R www-data:www-data /var/www/html/database
RUN chmod 755 /var/www/html/database

# Script para usar porta dinâmica do Render
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]