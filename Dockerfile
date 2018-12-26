FROM php:7.3-fpm-stretch

# Install Nginx
RUN apt-get update -qq && apt-get install -y --no-install-recommends -qq nginx

COPY ./tests/.docker/etc/nginx.conf /etc/nginx/nginx.conf
COPY ./tests/.docker/index.php /var/www/html/index.php

EXPOSE 80

# Nginx runs in background and php-fpm in the foreground
CMD nginx && php-fpm