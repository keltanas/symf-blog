#!/bin/bash
git pull
composer install -o --no-dev --prefer-dist
rm -rf ./app/cache/prod/
app/console doctrine:migrations:migrate --env=prod --no-debug --no-interaction
app/console assets:install --env=prod --no-debug
chown -R www-data:www-data ./*
