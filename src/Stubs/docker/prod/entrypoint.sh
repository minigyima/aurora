#!/bin/bash

source /mercury/index.sh

mkdir -p /srv/www/storage/app /srv/www/storage/framework/cache /srv/www/storage/framework/views
mkdir -p /srv/www/storage/app/public /srv/www/storage/logs /srv/www/bootstrap/cache
mkdir -p /srv/www/storage/api-docs /srv/www/storage/permission_cache /srv/www/storage/aurora

chmod -f -R 777 /srv/www/storage/framework /srv/www/storage/logs /srv/www/bootstrap/cache /srv/www/storage/api-docs /srv/www/storage/permission_cache /srv/www/storage/aurora
chmod 777 /srv/www/storage/app /srv/www/storage/app/public

export POSTGRES_USER=$database_user
export POSTGRES_PASSWORD=$database_password
export POSTGRES_DB=$database_name

$(which composer) dump-autoload
$PHP_PATH artisan optimize

$PHP_PATH artisan mercury:prepare-production

mkdir -p /var/log/supervisor
touch /var/log/supervisor/supervisord.log

mkdir -p /var/log/nginx
chown -R aurora:aurora /var/log/nginx

chown root:aurora /etc/nginx/nginx.conf
chmod 640 /etc/nginx/nginx.conf

# Why?
chown -R redis:redis /redis-data

su-exec aurora:1001 pnpm run --if-present aurora-prod-onboot

/usr/bin/supervisord -c /etc/supervisord.conf
