#!/usr/bin/env bash

dirnow=$(dirname $(realpath $0))
dirroot=$(realpath "$dirnow/../..")
printf "\n --- ROOT directory: $dirroot \n"


apt install ant 2>&1 -fy 2>&1 |
	grep -iv "newest\|cli interface\|reading\|building"

printf "\n\n --- build phar \n"
ant run-regular-tests-with-unscoped-phar

printf "\n\n --- build phar \n"
ant run-phar-specific-tests-with-scoped-phar

printf "\n\n --- update composer \n"
composer install --no-interaction

printf "\n\n --- test phpunit \n"
./phpunit --version
./phpunit -v --chunk-num 1000 --chunk-idx 1
