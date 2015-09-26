#!/bin/bash
git pull
composer update -o --no-dev --prefer-dist
rm -rf ./app/cache/prod/
app/console doctrine:migrations:diff --env=prod --no-debug --no-interaction
app/console assets:install --env=prod --no-debug
app/console assetic:dump --env=prod --no-debug
