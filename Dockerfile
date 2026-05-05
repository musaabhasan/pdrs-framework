FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql

RUN a2enmod rewrite headers

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html

WORKDIR /var/www/html

RUN mkdir -p storage/cache storage/logs \
    && chown -R www-data:www-data storage
