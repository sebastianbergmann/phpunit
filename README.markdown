PHPUnit
=======

PHPUnit is the de-facto standard for unit testing in PHP projects. It provides both a framework that makes the writing of tests easy as well as the functionality to easily run the tests and analyse their results.

Requirements
------------

* PHPUnit 3.6 requires PHP 5.2.7 (or later) but PHP 5.4.0 (or later) is highly recommended.
* [PHP_CodeCoverage](http://github.com/sebastianbergmann/php-code-coverage), the library that is used by PHPUnit to collect and process code coverage information, depends on [Xdebug](http://xdebug.org/) 2.0.5 (or later) but Xdebug 2.1.3 (or later) is highly recommended.

Installation
------------

PHPUnit should be installed using the PEAR Installer, the backbone of the [PHP Extension and Application Repository](http://pear.php.net/) that provides a distribution system for PHP packages.

Depending on your OS distribution and/or your PHP environment, you may need to install PEAR or update your existing PEAR installation before you can proceed with the following instructions. `sudo pear upgrade PEAR` usually suffices to upgrade an existing PEAR installation. The [PEAR Manual ](http://pear.php.net/manual/en/installation.getting.php) explains how to perform a fresh installation of PEAR.

The following two commands (which you may have to run as `root`) are all that is required to install PHPUnit using the PEAR Installer:

    pear config-set auto_discover 1
    pear install pear.phpunit.de/PHPUnit

After the installation you can find the PHPUnit source files inside your local PEAR directory; the path is usually `/usr/lib/php/PHPUnit`.

Documentation
-------------

The documentation for PHPUnit is available in different formats:

* [English, multiple HTML files](http://www.phpunit.de/manual/3.6/en/index.html)
* [English, single HTML file](http://www.phpunit.de/manual/3.6/en/phpunit-book.html)
* [English, PDF](http://www.phpunit.de/manual/3.6/en/phpunit-book.pdf)
* [English, ePub](http://www.phpunit.de/manual/3.6/en/phpunit-book.epub)
* [French, multiple HTML files](http://www.phpunit.de/manual/3.6/fr/index.html)
* [French, single HTML file](http://www.phpunit.de/manual/3.6/fr/phpunit-book.html)
* [French, PDF](http://www.phpunit.de/manual/3.6/fr/phpunit-book.pdf)
* [French, ePub](http://www.phpunit.de/manual/3.6/fr/phpunit-book.epub)
* [Japanese, multiple HTML files](http://www.phpunit.de/manual/3.6/ja/index.html)
* [Japanese, single HTML file](http://www.phpunit.de/manual/3.6/ja/phpunit-book.html)
* [Japanese, PDF](http://www.phpunit.de/manual/3.6/ja/phpunit-book.pdf)
* [Japanese, ePub](http://www.phpunit.de/manual/3.6/ja/phpunit-book.epub)

IRC
---

The [#phpunit channel on the Freenode IRC network](irc://freenode.net/phpunit) is a place to chat about PHPUnit.

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

    pear channel-discover pear.symfony-project.com
    pear install pear.symfony-project.com/YAML

The `phpunit/phpunit.php` script can be used to invoke the PHPUnit test runner.

The following commands can be used to check out the appropriate branches for PHPUnit 3.6:

    cd phpunit && git checkout 3.6 && cd ..
    cd dbunit && git checkout 1.1 && cd ..
    cd php-code-coverage && git checkout 1.1 && cd ..
    cd phpunit-mock-objects && git checkout 1.1 && cd ..
    cd phpunit-selenium && git checkout 1.1 && cd ..

Contributing
------------

Contributions to PHPUnit, its related modules and its documentation are always welcome and best done using GitHub pull request.

Please note that the `3.6.` branch is closed for features and that pull requests should to be based on `master` or the `3.7.` once it exists. 

We are trying to keep BC breaks in PHPUnit 3.7 to a absolute minimum so please take this into account when proposing changes.

Due to time constraints we are not always able to respond as quickly as we'd like to so please do not take delays personal and feel free to remind us here or on IRC if you feel that we forgot to respond.

### List of Contributors

Thanks to everyone that has contributed to PHPUnit! You can find a detailed contributors list on every PHPUnit related package on GitHub. This list shows only the bigger components:

- [PHPUnit core](https://github.com/sebastianbergmann/phpunit/graphs/contributors)
- [PHP code coverage](https://github.com/sebastianbergmann/php-code-coverage/graphs/contributors)
- [PHPUnit mock objects](https://github.com/sebastianbergmann/phpunit-mock-objects/graphs/contributors)

A very special thanks to everyone that has contributed to the documentation and helped maintaining the translations:

- [PHPUnit Documentation](https://github.com/sebastianbergmann/phpunit-documentation/graphs/contributors)

### Running the test suite(s)

It's not possible to use a system wide installed version of PHPUnit to run the test suite of a git checkout. Because of that is is necessary to change the include paths like describe above.

This can be achieved with a small wrapper script designed to work with every module in the PHPUnit stack.

Note that you might have to change the path to your pear installation here pointing to `/usr/local/lib/php`. You can find it using `pear config-show | grep php_dir`

**Linux/Mac**

`run-tests.sh`

> \#!/bin/bash
>
> php -d include_path='.:../phpunit/:../dbunit/:../php-code-coverage/:../php-file-iterator/:../php-invoker/:../php-text-template/:../php-timer:../php-token-stream:../phpunit-mock-objects/:../phpunit-selenium/:../phpunit-story/:/usr/local/lib/php' ../phpunit/phpunit.php $*

**Windows**

`run-tests.bat`

> @echo off
>
> php -d include_path='.;../phpunit/;../dbunit/;../php-code-coverage/;../php-file-iterator/;../php-invoker/;../php-text-template/;../php-timer;../php-token-stream;../phpunit-mock-objects/;../phpunit-selenium/;../phpunit-story/;C:/Program Files/PHP/pear' ../phpunit/phpunit.php %*

