#!/bin/bash
set -e

if [ ! -d /var/www/html/vendor ]; then
    echo "Installing composer dependencies..."
    php /var/www/composer.phar --working-dir=/var/www/html install --no-interaction
fi

exec "$@"
