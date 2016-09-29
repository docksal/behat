FROM alpine:edge

MAINTAINER Leonid Makarov <leonid.makarov@ffwagency.com>

RUN apk --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/edge/community  add \
    bash \
    curl \
    git \
    unzip \
    php7 \
    php7-xml \
    php7-zip \
    php7-xmlreader \
    php7-zlib \
    php7-opcache \
    php7-mcrypt \
    php7-openssl \
    php7-curl \
    php7-json \
    php7-dom \
    php7-phar \
    php7-mbstring \
    php7-ctype \
    && rm -rf /var/cache/apk/*

RUN \
	# Symlinks php7
	ln -s /usr/bin/php7 /usr/bin/php

ENV COMPOSER_VERSION 1.2.0

RUN \
	# Install Composer
	curl -sSL https://github.com/composer/composer/releases/download/$COMPOSER_VERSION/composer.phar -o /usr/local/bin/composer && \
	chmod +x /usr/local/bin/composer

COPY composer.json /opt/behat/composer.json

RUN \
	# Install Behat
	cd /opt/behat && \
	composer install 2>&1

ENV PATH $PATH:/opt/behat/bin

WORKDIR /src

ENTRYPOINT ["behat"]
