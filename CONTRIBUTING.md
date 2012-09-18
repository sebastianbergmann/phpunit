Contributing to PHPUnit
=======================

Contributions to PHPUnit, its related modules, and its documentation are always welcome. You make our lifes easier by sending us your contributions through GitHub pull requests.

Please note that the `3.6.` branch is closed for features and that pull requests should to be based on `master` or the `3.7.` once it exists.

We are trying to keep backwards compatibility breaks in PHPUnit 3.7 to an absolute minimum so please take this into account when proposing changes.

Due to time constraints, we are not always able to respond as quickly as we would like. Please do not take delays personal and feel free to remind us here or on IRC if you feel that we forgot to respond.

Using PHPUnit From a Git Checkout
---------------------------------

The following commands can be used to perform the initial checkout of PHPUnit and its dependencies from Git:

    mkdir phpunit && cd phpunit
    git clone git://github.com/sebastianbergmann/phpunit.git
    git clone git://github.com/sebastianbergmann/dbunit.git
    git clone git://github.com/sebastianbergmann/php-file-iterator.git
    git clone git://github.com/sebastianbergmann/php-text-template.git
    git clone git://github.com/sebastianbergmann/php-code-coverage.git
    git clone git://github.com/sebastianbergmann/php-token-stream.git
    git clone git://github.com/sebastianbergmann/php-timer.git
    git clone git://github.com/sebastianbergmann/phpunit-mock-objects.git
    git clone git://github.com/sebastianbergmann/phpunit-selenium.git
    git clone git://github.com/sebastianbergmann/phpunit-story.git
    git clone git://github.com/sebastianbergmann/php-invoker.git

The `dbunit`, `php-code-coverage`, `php-file-iterator`, `php-text-template`, `php-timer`, `php-token-stream`, `phpunit`, `phpunit-mock-objects`, `phpunit-selenium`, `phpunit-story`, and `php-invoker` directories need to be added to the `include_path`.

In addition to the checkouts listed above, the YAML component that is provided by the Symfony project is required:

    pear install pear.symfony.com/Yaml

The `phpunit/phpunit.php` script can be used to invoke the PHPUnit test runner.

Running the test suite(s)
-------------------------

It is not possible to use a system-wide installed version of PHPUnit to run the test suite of a Git checkout. Because of that is is necessary to change the `include_paths` as described below.

This can be achieved with a small wrapper script designed to work with every module in the PHPUnit stack.

Note that you might have to change the path to your PEAR installation here pointing to `/usr/local/lib/php`. You can find it using `pear config-show | grep php_dir`.

### Linux / MacOS X

    #!/bin/bash
    php -d include_path='.:../phpunit/:../dbunit/:../php-code-coverage/:../php-file-iterator/:../php-invoker/:../php-text-template/:../php-timer:../php-token-stream:../phpunit-mock-objects/:../phpunit-selenium/:../phpunit-story/:/usr/local/lib/php' ../phpunit/phpunit.php $*

### Windows

    @echo off
    php -d include_path='.;../phpunit/;../dbunit/;../php-code-coverage/;../php-file-iterator/;../php-invoker/;../php-text-template/;../php-timer;../php-token-stream;../phpunit-mock-objects/;../phpunit-selenium/;../phpunit-story/;C:/Program Files/PHP/pear' ../phpunit/phpunit.php %*
