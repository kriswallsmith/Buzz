FROM php:7.3-fpm-stretch

# Install packages: Nginx, git etc
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx nano less

COPY ./tests/.docker/etc/nginx.conf /etc/nginx/nginx.conf
COPY ./tests/.docker /var/www/html

EXPOSE 80
STOPSIGNAL SIGTERM

CMD ["nginx", "-g", "daemon off;"]