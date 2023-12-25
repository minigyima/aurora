#!/bin/bash
set -e

docker-php-ext-enable xdebug
touch /var/log/xdebug.log
mkdir -p /var/log/supervisor
touch /var/log/supervisor/supervisord.log

# Boot PHP-FPM with Nginx
/usr/bin/supervisord -c /etc/supervisord.conf
