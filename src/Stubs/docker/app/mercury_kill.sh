#!/bin/bash

xdebug_kill() {
    if [ -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ]; then
        echo "# Killing Xdebug..."
        rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
    fi
}

xdebug_kill

echo "# Killing FPM..."
killall supervisord
echo "# Killing Octane..."
/usr/local/bin/php -d xdebug.mode=off /srv/www/artisan octane:stop
