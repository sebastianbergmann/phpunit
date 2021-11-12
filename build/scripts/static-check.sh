#!/usr/bin/env bash

dirnow=$(dirname $(realpath $0))
dirroot=$(realpath "$dirnow/../..")
printf "\n --- ROOT directory: $dirroot \n"

chmod +x $dirroot/tools/psalm $dirroot/tools/php-cs-fixer

printf "\n\n --- execute php-cs-fixer \n"
$dirroot/tools/php-cs-fixer fix

printf "\n\n --- execute psalm: delete cache \n"
rm -rf $dirroot/.psalm/cache

printf "\n\n --- execute psalm: static \n"
$dirroot/tools/psalm --config=.psalm/config.xml --no-cache --clear-global-cache
$dirroot/tools/psalm --config=.psalm/static-analysis.xml

printf "\n\n --- execute psalm: find ununsed suppress \n"
$dirroot/tools/psalm --config=.psalm/config.xml --no-cache --clear-global-cache
$dirroot/tools/psalm --config=.psalm/config.xml --find-unused-psalm-suppress

printf "\n\n --- execute psalm: last \n"
$dirroot/tools/psalm --config=.psalm/config.xml --no-cache --clear-global-cache
$dirroot/tools/psalm --config=.psalm/config.xml
