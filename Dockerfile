FROM php:7.3-fpm-stretch

# Install composer
RUN curl -sS https://getcomposer.org/installer | php \
  && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer

# Install Nginx
RUN apt update && apt install -y --no-install-recommends nginx squid nano less
COPY ./tests/.docker/etc/nginx.conf /etc/nginx/nginx.conf
RUN /etc/init.d/nginx restart && /etc/init.d/squid restart

COPY ./tests/.docker /var/www/html
WORKDIR /var/www/html
RUN composer update
RUN ./vendor/bin/http_test_server > /dev/null 2>&1 &