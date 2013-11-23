#!/bin/bash
git pull
composer update
app/console cache:clear --env=prod --no-debug
app/console cache:warmup --env=prod --no-debug
app/console doctrine:migrations:diff --env=prod --no-debug --no-interaction
app/console assets:install --env=prod --no-debug
app/console assetic:dump --env=prod --no-debug
