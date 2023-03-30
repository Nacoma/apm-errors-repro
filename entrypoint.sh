#!/usr/bin/env sh

su -c composer install -s /bin/sh www-data
su -c cp .env.example .env -s /bin/sh www-data
su -c php artisan key:generate -s /bin/sh www-data
su -c php artisan storage:link -s /bin/sh www-data

exec "php-fpm"
