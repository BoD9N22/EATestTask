#!/bin/sh

set -e

composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan schedule:work
