FROM php:7.3-apache-stretch
MAINTAINER Jason Gillyon <hello@jasongillyon.co.uk>
RUN a2enmod rewrite
RUN a2enmod headers
RUN a2enmod http2
RUN apt-get update && \
    apt-get -y install curl nano libpng-dev libzip-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install mysqli gd zip opcache calendar bcmath
COPY config/php-overrides.ini /usr/local/etc/php/conf.d/
COPY . /var/www/html/
RUN cd /var/www/html/
EXPOSE 80 443
