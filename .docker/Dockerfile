#-----------------------------------------------------------
# Docker Image with PHP runtime for wowza data collector
#-----------------------------------------------------------
FROM php:7-fpm-alpine
MAINTAINER Paulo Costa <paulo.costa@fccn.pt>

#---- prepare environment variables
ENV APP_ROOT /app

#add testing and community repositories
RUN echo '@testing http://nl.alpinelinux.org/alpine/edge/testing' >> /etc/apk/repositories && \
  echo '@community http://nl.alpinelinux.org/alpine/edge/community' >> /etc/apk/repositories && \
  echo '@edge http://nl.alpinelinux.org/alpine/edge/main' >> /etc/apk/repositories && \
  apk update && apk upgrade --no-cache --available
RUN apk add --upgrade apk-tools@edge

#additional packages
RUN apk add --no-cache --update curl git

#add application user and group
RUN addgroup -g 1000 application && adduser -u 1000 -G application -D application

#-prepare startup
ADD build/entrypoint.sh /tmp/entrypoint.sh
RUN chmod 755 /tmp/entrypoint.sh

#---- Tools

WORKDIR /tmp
#-install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
  php composer-setup.php && \
  php -r "unlink('composer-setup.php');" && cp composer.phar /usr/local/bin/composer


WORKDIR $APP_ROOT
#change ownership of application
RUN chown -R application:application $APP_ROOT
USER application
#install application
RUN git clone https://github.com/fccn/wowza-api-data-collector.git .
RUN composer install

USER root
#-remove unecessary libs
RUN apk del --no-cache git

#USER application
CMD ["/tmp/entrypoint.sh"]
