FROM php:8.1-fpm-alpine

ARG UID
ARG GID

LABEL role="php"

RUN apk update && apk add --no-cache --virtual .build-deps  \
    zlib-dev \
    libpng-dev \
    libxml2-dev \
    bzip2-dev \
    zip
# Add Production Dependencies
RUN apk add --update --no-cache \
    jpegoptim \
    pngquant \
    optipng \
    icu-dev \
    freetype-dev \
    libzip-dev \
    gmp-dev

RUN docker-php-ext-configure \
    opcache --enable-opcache && \
    docker-php-ext-install \
        opcache \
        sockets \
        intl \
        bz2 \
        pcntl \
        bcmath \
        zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN curl -L -o agent.apk https://github.com/elastic/apm-agent-php/releases/download/v1.6.1/apm-agent-php_1.6.1_all.apk && \
    apk add --allow-untrusted agent.apk


RUN apk add shadow
# create a user with our userid, its okay if its already in use, see the -o flag
RUN useradd -o -u $UID -ms /bin/bash dockeruser
# add the user to the group
RUN addgroup dockeruser www-data


WORKDIR /app

COPY entrypoint.sh /entrypoint.sh

ENV ELASTIC_APM_ENABLED=true
ENV ELASTIC_APM_LOG_LEVEL=TRACE
ENV ELASTIC_APM_SERVER_URL=http://127.0.0.1:8200

USER www-data

ADD --chown=www-data:www-data . /app

ENTRYPOINT ["/entrypoint.sh"]
