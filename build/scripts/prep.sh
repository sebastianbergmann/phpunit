#!/usr/bin/env bash

dirnow=$(dirname $(realpath $0))
dirroot=$(realpath "$dirnow/../..")
printf "\n --- ROOT directory: $dirroot \n"


#--- global install composer
printf "\n --- install composer globally "
if [[ ! -e /usr/local/bin/composer ]]; then
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	php composer-setup.php
	php -r "unlink('composer-setup.php');"
	mv composer.phar /usr/local/bin/composer
fi

chmod +x /usr/local/bin/composer

#printf "\n\n --- update composer \n"
/usr/local/bin/composer install --no-interaction
/usr/local/bin/composer update --no-interaction
#
#printf "\n\n --- execute php-cs-fixer \n"
#chmod +x $dirroot/tools/php-cs-fixer
#$dirroot/tools/php-cs-fixer fix
#
#printf "\n\n --- execute psalm: static \n"
#rm -rf $dirroot/.psalm/cache
#chmod +x $dirroot/tools/psalm
#$dirroot/tools/psalm --config=.psalm/static-analysis.xml
#
#printf "\n\n --- execute psalm: next \n"
#$dirroot/tools/psalm --config=.psalm/config.xml --stats --shepherd

printf "\n\n --- execute sanity check \n"
/bin/bash $dirroot/build/scripts/sanity-check

printf "\n\n --- execute coverage check \n"
$dirroot/phpunit --coverage-clover=coverage.xml

printf "\n\n --- generate global assert wrappers \n"
php $dirroot/build/scripts/generate-global-assert-wrappers.php

printf "\n\n --- generate current diff \n"
git diff current.diff || echo "Run 'php build/scripts/generate-global-assert-wrappers.php' to regenerate global assert wrappers!"

printf "\n\n --- build phar \n"
ant run-regular-tests-with-unscoped-phar

printf "\n\n --- build phar \n"
ant run-phar-specific-tests-with-scoped-phar
