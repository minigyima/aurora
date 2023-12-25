FROM php:8.3-alpine3.19

WORKDIR /srv/www
#aaa
RUN echo 'Installing packages...'
RUN apk update && \
    apk add npm \
    nodejs \
    bzip2-dev \
    coreutils \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    postgresql-client \
    figlet \
    mc \
    vim \
    unzip \
    libpq-dev \
    libzip-dev \
    libxml2-dev \
    busybox-extras \
    supervisor \
    bash

RUN echo "export PROC_COUNT=`nproc`" >> /envfile
RUN . /envfile; echo "Processors: $PROC_COUNT --> -j$PROC_COUNT"

RUN echo 'Installing PHP extensions...'
ENV MAKEFLAGS="-j$(nproc)"
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg
RUN . /envfile; docker-php-ext-install -j$PROC_COUNT bcmath \
    bz2 \
    exif \
    opcache \
    pdo_pgsql \
    zip \
    pcntl \
    gd \
    dom \
    shmop \
    sysvmsg \
    sysvsem \
    sysvshm


RUN apk --no-cache add pcre-dev imagemagick-dev linux-headers imagemagick-dev ${PHPIZE_DEPS}
ENV MAKEFLAGS="-j$(nproc)"
# PECL extensions
RUN MAKEFLAGS="-j$(nproc)" pecl install redis
RUN MAKEFLAGS="-j$(nproc)" pecl install imagick
RUN MAKEFLAGS="-j$(nproc)" pecl install xdebug
RUN MAKEFLAGS="-j$(nproc)" pecl install openswoole-22.1.2

RUN docker-php-ext-enable redis imagick gd dom openswoole

RUN apk add ca-certificates icu-dev

RUN . /envfile; docker-php-ext-configure intl && docker-php-ext-install -j$PROC_COUNT intl

RUN echo "export COMPOSER_ALLOW_SUPERUSER=1" > /etc/profile.d/composer.sh && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

RUN npm install -g pnpm@latest

RUN touch /mercury.container

# Config 'n stuff
COPY php.ini /usr/local/etc/php/
COPY nginx.conf /etc/nginx/
COPY xdebug.ini /usr/local/etc/php/conf.d/
COPY opcache.ini /usr/local/etc/php/conf.d/
COPY supervisord.conf /etc/
COPY mercury.sh /
COPY mercury_debug.sh /
COPY mercury_swoole.sh /
COPY mercury_kill.sh /
COPY artisan.sh /usr/sbin/artisan
RUN chmod +x /usr/sbin/artisan
RUN chmod 777 /mercury.sh /mercury_debug.sh /mercury_swoole.sh /mercury_kill.sh

CMD ["bash", "/mercury.sh"]
