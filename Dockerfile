FROM php:8.3-apache

RUN docker-php-ext-install mysqli \
    && a2enmod headers

WORKDIR /var/www/html

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache/app.conf /etc/apache2/conf-enabled/app.conf
COPY docker/php/app.ini /usr/local/etc/php/conf.d/app.ini
COPY --chown=www-data:www-data src/ ./src/
COPY --chown=www-data:www-data templates/ ./templates/
COPY --chown=www-data:www-data public/ ./public/

EXPOSE 80

CMD ["apache2-foreground"]
