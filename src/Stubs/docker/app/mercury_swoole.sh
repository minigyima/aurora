#!/bin/bash
set -e

xdebug_kill() {
    if [ -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ]; then
        rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
    fi
}

xdebug_kill

/usr/local/bin/php artisan octane:start --watch --host=0.0.0.0 --port=80
