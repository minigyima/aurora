#!/bin/bash

source /mercury/setenv.sh
source /mercury/kill_functions.sh
source /mercury/logger.sh
source /mercury/package_managers.sh
source /mercury/lock_functions.sh
source /mercury/laravel_init.sh

swoole_start() {
    log_info "Starting aurora via OpenSwoole..."
    xdebug_kill
    $PHP_PATH artisan octane:start --watch --host=0.0.0.0 --port=80
}

debug_start() {
    log_info "Starting aurora in debug mode..."
    log_trace "Enabling Xdebug..."
    echo "zend_extension=xdebug" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
    touch /var/log/xdebug.log

    log_trace "Setting up supervisord..."
    mkdir -p /var/log/supervisor
    touch /var/log/supervisor/supervisord.log

    log_trace "Boot PHP-FPM with Nginx..."
    /usr/bin/supervisord -c /etc/supervisord.conf
}

boot() {
    log_info "Handing over control to Aurora..."
    while true; do $PHP_PATH artisan mercury:boot && break; done
}

firstboot_project() {
   log_trace "Checking if this is the first start of Mercury..."
   if [ ! -f "/root/mercury-app-$app_name.lock" ]; then
       log_info "Preparing $app_name for first run..."
       composer
       npm
       laravel_env
       laravel_cache
       set_lock
   fi
}

READY=0

firstboot_db() {
    log_trace "Checking if this database has been prepared..."

    if [ ! -f "/root/mercury-db-$database_hash.lock" ]; then
        log_info "Preparing database for first run..."
        if [ "$database_driver" == "pgsql" ]; then
                log_trace "Waiting for database to be ready..."
                       while [ "$READY" -eq 0 ]; do
                           if pg_isready -d "postgres://$database_host";
                           then READY=1;
                           else log_trace "Database not ready yet, waiting for 1 seconds...";
                           fi
                           sleep 1
                       done
                       log_info "Database is ready, migrating"
        fi
        laravel_migrate
        set_db_lock
    fi
}

firstboot() {
    firstboot_project
    firstboot_db
    log_success "Done! Starting $app_name..."
}
