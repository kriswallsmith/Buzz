FROM php:7.3-fpm-stretch

# Install composer
RUN curl -sS https://getcomposer.org/installer | php \
  && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer

# Install packages: Nginx, Squid, git etc
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx squid git unzip libzip-dev nano less

RUN docker-php-ext-install zip

COPY ./tests/.docker/etc/nginx.conf /etc/nginx/nginx.conf
RUN /etc/init.d/nginx restart && /etc/init.d/squid restart

COPY ./tests/.docker /var/www/html
COPY . /var/www/app

WORKDIR /var/www/app
RUN composer update
RUN php -S 0.0.0.0:10000 -t "./vendor/php-http/client-integration-tests/fixture" > /dev/null 2>&1 &
