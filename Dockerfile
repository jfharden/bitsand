FROM php:5.6-apache

LABEL maintainer="jfharden@gmail.com"

RUN apt-get update && apt-get install -y \
    ssmtp \
    zlib1g-dev \
  && docker-php-ext-install \
    mysql \
    zip \
  && rm -rf /var/lib/{apt,dpkg,cache,log}

COPY docker-config/system/ /

COPY --chown=www-data:www-data . /var/www/html/

COPY --chown=www-data:www-data docker-config/bitsand/inc/ inc/

COPY --chown=www-data:www-data inc/admin_dist.css inc/admin.css
COPY --chown=www-data:www-data inc/body_dist.css inc/body.css
COPY --chown=www-data:www-data inc/help_dist.css inc/help.css
COPY --chown=www-data:www-data terms_dist.php terms.php

