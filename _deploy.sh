#!/bin/sh

git pull origin main

/opt/php83/bin/php /usr/bin/composer install --no-interaction --optimize-autoloader --no-dev

/opt/php83/bin/php artisan migrate --force

/opt/php83/bin/php artisan optimize:clear
/opt/php83/bin/php artisan optimize
