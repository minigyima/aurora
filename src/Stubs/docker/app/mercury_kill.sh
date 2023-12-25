#!/bin/bash

xdebug_kill() {
    if [ -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ]; then
        rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
    fi
}

xdebug_kill

killall -9 supervisord
/usr/local/bin/php -d xdebug.mode=off /srv/www/artisan octane:stop
/usr/local/bin/php -d xdebug.mode=off /srv/www/artisan octane:stop
