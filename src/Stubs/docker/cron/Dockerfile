FROM mercury
WORKDIR /srv/www/

RUN pecl uninstall xdebug
RUN bash /mercury_kill.sh

COPY crontabs /var/spool/cron/crontabs/

CMD ["crond", "-f", "-l", "8", "-L", "/dev/stdout"]
