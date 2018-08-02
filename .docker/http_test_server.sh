#!/bin/bash

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

php -S 0.0.0.0:10000 -t "$DIR/../vendor/php-http/client-integration-tests/fixture"
