#!/bin/sh
set -e

PHP=/opt/php83/bin/php

git pull origin main

$PHP /usr/bin/composer install --no-interaction --optimize-autoloader --no-dev

$PHP artisan migrate --force

$PHP artisan optimize:clear
