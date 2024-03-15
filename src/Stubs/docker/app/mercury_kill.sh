#!/bin/bash

if [ -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ]; then
    rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
fi
killall supervisord 2>&1 /dev/null
/usr/local/bin/php -d xdebug.mode=off /srv/www/artisan octane:stop 2>&1 /dev/null
