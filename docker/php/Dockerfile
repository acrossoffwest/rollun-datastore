FROM php:7.0-fpm

MAINTAINER Yurij Karpov <acrossoffwest@gmail.com>

WORKDIR /var/www/html

RUN apt-get update

RUN apt-get -y install cron zip unzip libzip-dev

RUN docker-php-ext-install zip
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql

COPY ./crontab_schedule /tmp/crontab_schedule
RUN crontab /tmp/crontab_schedule && rm /tmp/crontab_schedule

RUN apt-get -y install supervisor && \
  mkdir -p /var/log/supervisor && \
  mkdir -p /etc/supervisor

COPY ./supervisor /etc/supervisor

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
rm composer-setup.php

CMD ["service", "cron", "start"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
