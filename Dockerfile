FROM php:8.2-apache
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
COPY apache/apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

