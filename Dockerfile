FROM php:7.2-fpm-stretch

ENV SQUID_CACHE_DIR=/var/spool/squid \
    SQUID_LOG_DIR=/var/log/squid \
    SQUID_USER=proxy

RUN apt-get update \
 && DEBIAN_FRONTEND=noninteractive apt-get install -yq nginx git zip libzip-dev \
 && DEBIAN_FRONTEND=noninteractive apt-get install -yq nano less \
 && rm -rf /var/lib/apt/lists/*

RUN pecl install zip \
    && docker-php-ext-enable zip

RUN apt-get clean && rm -rf /var/lib/apt/lists/

RUN mkdir -p /home/docker /app
ADD .docker /home/docker
ADD / /app
ADD tests/server.php /var/www
RUN chmod -R 755 /home/docker/*.sh

RUN curl --silent --show-error https://getcomposer.org/installer | php && cp composer.phar /usr/bin/composer

