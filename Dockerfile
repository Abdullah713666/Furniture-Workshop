FROM php:8.2-apache
RUN apt-get update && apt-get install -y libzip-dev && docker-php-ext-install pdo_mysql && a2enmod rewrite && apt-get clean
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
