#!/usr/bin/env bash

OWNER="$1"
PHPVERSION="$2"
PHPPATH="/home/$OWNER/.phpenv/versions/$PHPVERSION/etc"

cp $PHPPATH/php-fpm.conf.default $PHPPATH/php-fpm.conf

if [ -f $PHPPATH/php-fpm.d/www.conf.default ]; then
    echo "# PHP 7"
	  cp $PHPPATH/php-fpm.d/www.conf.default $PHPPATH/php-fpm.d/www.conf
elif [ -f $PHPPATH/php-fpm.conf.default ]; then
    echo "# PHP 5"
    cp $PHPPATH/php-fpm.conf.default $PHPPATH/php-fpm.conf
fi

echo "cgi.fix_pathinfo = 1" >> $PHPPATH/php.ini
