FROM alpine:3.8

ENV PHP_VERSION nightly
ENV PHP_INI_DIR /usr/local/etc/php

RUN set -xe \
    && apk add --no-cache --virtual .persistent-deps \
        ca-certificates \
		curl \
		tar \
		xz \
		git

RUN set -xe \
    && apk add --no-cache --virtual .build-deps \
		autoconf \
        file \
        g++ \
        gcc \
        libc-dev \
        make \
        pkgconf \
        re2c \
		curl-dev \
		libedit-dev \
		libxml2-dev \
		libressl-dev \
		sqlite-dev \
		bison \
        libbz2 \
        bzip2-dev \
	&& mkdir -p $PHP_INI_DIR/conf.d \
    && git clone https://github.com/php/php-src.git /usr/src/php \
    && cd /usr/src/php \
    && ./buildconf \
    && ./configure \
        --with-config-file-path="$PHP_INI_DIR" \
        --with-config-file-scan-dir="$PHP_INI_DIR/conf.d" \
        --disable-cgi \
        --enable-ftp \
        --enable-mbstring \
        --enable-mysqlnd \
        --with-curl \
        --with-libedit \
        --with-openssl \
        --with-zlib \
        --with-bz2 \
        --without-pear \
    && make -j"$(getconf _NPROCESSORS_ONLN)" \
    && make install \
    && rm -rf /usr/src/php \
    && runDeps="$( \
		scanelf --needed --nobanner --recursive /usr/local \
			| awk '{ gsub(/,/, "\nso:", $2); print "so:" $2 }' \
			| sort -u \
			| xargs -r apk info --installed \
			| sort -u \
	    )" \
	&& apk add --no-cache --virtual .php-rundeps $runDeps \
	&& apk del .build-deps

CMD ["php", "-a"]
