FROM mercury
WORKDIR /srv/www/

RUN pecl uninstall xdebug
RUN bash /mercury_kill.sh

CMD ["php", "artisan", "mercury:boot-horizon"]
