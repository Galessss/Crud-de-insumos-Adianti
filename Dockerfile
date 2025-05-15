FROM php:8.2-apache

# Instalar dependências necessárias para SQLite
RUN apt-get update && apt-get install -y libsqlite3-dev

# Instalar extensão PDO SQLite
RUN docker-php-ext-install pdo_sqlite

COPY docker/php.ini /usr/local/etc/php/

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/

RUN chmod -R 755 /var/www/html