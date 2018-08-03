#!/bin/bash

cd /app

# Variables
: "${TEST_COMMAND:=composer test}"
: "${COMPOSER_FLAGS:=--prefer-dist}"

/usr/local/sbin/php-fpm > /dev/null 2>&1 &
/usr/sbin/nginx -c /home/docker/etc/nginx.conf > /dev/null 2>&1 &
./.docker/http_test_server.sh > /dev/null 2>&1 &

function successOrExit {
    "$@"
    local status=$?
    if [ $status -ne 0 ]; then
        echo "Exited with code $status"
        exit $status
    fi
}

# Install
if ! [ -z "$DEPENDENCIES" ]; then composer require --no-update ${DEPENDENCIES}; fi;
#To be removed when this issue will be resolved: https://github.com/composer/composer/issues/5355
if [[ "$COMPOSER_FLAGS" == *"--prefer-lowest"* ]]; then composer update --prefer-dist --no-interaction --prefer-stable --quiet; fi

successOrExit composer update ${COMPOSER_FLAGS} --prefer-dist --no-interaction
successOrExit ./vendor/bin/simple-phpunit install
successOrExit composer validate --strict --no-check-lock
successOrExit timeout 120 $TEST_COMMAND

if [[ "$CS" == "true" ]]; then vendor/bin/php-cs-fixer fix --config=.php_cs --verbose --diff --dry-run; fi

# After
if [[ $COVERAGE = true && $TRAVIS = true ]]; then wget https://scrutinizer-ci.com/ocular.phar && php ocular.phar code-coverage:upload --format=php-clover ./build/coverage.xml; fi
