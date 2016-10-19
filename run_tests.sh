#!/usr/bin/env bash
./app/console --env=test -n doctrine:database:create
./app/console --env=test -n doctrine:schema:create
./app/console --env=test -n -v doctrine:fixtures:load
./bin/behat
./app/console --env=test -n doctrine:database:drop --force