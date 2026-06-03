FROM php:8.2-cli

RUN apt-get update && apt-get install -y libzip-dev \
    && docker-php-ext-install pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/
WORKDIR /var/www/html

EXPOSE ${PORT:-8080}

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} router.php"]
