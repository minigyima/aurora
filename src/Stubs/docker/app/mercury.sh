#!/bin/bash
set -e

cd /srv/www
env_file=".env"
app_name=$(cat $env_file | grep -m 1 APP_NAME= | sed -E 's/^APP_NAME=//')
mercury_version="0.1"

composer() {
    echo "# Installing dependencies using Composer..."
    $(which composer) update
}

npm() {
    echo "# Installing dependencies using pnpm..."
    rm -rf node_modules
    pnpm update
    pnpm install chokidar
}

laravel_cache() {
    echo "# Cleaning Laravel caches..."
    /usr/local/bin/php artisan config:cache
    /usr/local/bin/php artisan optimize:clear
}

laravel_env() {
    if [ ! -f .env ]; then
        echo "# No .env file found! Copying example..."
        cp .env.example .env
        # I shall not comment this one lol
        chmod 666 .env
        echo "# Generating app key..."
        /usr/local/bin/php artisan key:generate
    fi
}

laravel_migrate() {
    echo "# Running migrations..."
    /usr/local/bin/php artisan migrate --force
}

set_lock() {
    touch "/root/mercury-$app_name.lock"
}

firstboot() {
    # Check if this is the first start of Mercury...
    if [ ! -f "/root/mercury-$app_name.lock" ]; then
        echo "# Preparing $app_name for first run..."
        composer
        npm
        laravel_env
        laravel_cache
        laravel_migrate
        set_lock

        echo "# Done! Starting $app_name..."
    fi
}

boot() {
    echo "# Handing over control to Aurora..."
    while true; do /usr/local/bin/php artisan mercury:boot && break; done

}

# Totally dumb, and even more unnescessary. To be fair, it does look cool...
figlet "Mercury"
echo ""
echo "Mercury - Docker Runtime for Aurora (version $mercury_version)"
echo "Booting your application..."

firstboot
boot
