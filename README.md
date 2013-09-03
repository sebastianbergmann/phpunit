# PHPUnit

PHPUnit is the de-facto standard for unit testing in PHP projects. It provides both a framework that makes the writing of tests easy as well as the functionality to easily run the tests and analyse their results.

[![Build Status](https://travis-ci.org/sebastianbergmann/phpunit.png?branch=3.7)](https://travis-ci.org/sebastianbergmann/phpunit)

## Requirements

* PHPUnit 3.7 requires PHP 5.3.3 (or later) but PHP 5.4.6 (or later) is highly recommended.
* [PHP_CodeCoverage](http://github.com/sebastianbergmann/php-code-coverage), the library that is used by PHPUnit to collect and process code coverage information, depends on [Xdebug](http://xdebug.org/) 2.0.5 (or later) but Xdebug 2.2.1 (or later) is highly recommended.

## Installation

### PHP Archive (PHAR)

The easiest way to obtain PHPUnit is to download a [PHP Archive (PHAR)](http://php.net/phar) that has all required (as well as some optional) dependencies of PHPUnit bundled in a single file:

    wget https://phar.phpunit.de/phpunit.phar
    chmod +x phpunit.phar
    mv phpunit.phar /usr/local/bin/phpunit

You can also immediately use the PHAR after you have downloaded it, of course:

    wget https://phar.phpunit.de/phpunit.phar
    php phpunit.phar

### Composer

Simply add a dependency on `phpunit/phpunit` to your project's `composer.json` file if you use [Composer](http://getcomposer.org/) to manage the dependencies of your project. Here is a minimal example of a `composer.json` file that just defines a development-time dependency on PHPUnit 3.7:

    {
        "require-dev": {
            "phpunit/phpunit": "3.7.*"
        }
    }

For a system-wide installation via Composer, you can run:

    composer global require 'phpunit/phpunit=3.7.*'

Make sure you have `~/.composer/vendor/bin/` in your path.

### PEAR Installer

The following two commands (which you may have to run as `root`) are all that is required to install PHPUnit using the PEAR Installer:

    pear config-set auto_discover 1
    pear install pear.phpunit.de/PHPUnit

## Documentation

The documentation for PHPUnit is available in different formats:

* [English, multiple HTML files](http://www.phpunit.de/manual/3.7/en/index.html)
* [English, single HTML file](http://www.phpunit.de/manual/3.7/en/phpunit-book.html)
* [English, PDF](http://www.phpunit.de/manual/3.7/en/phpunit-book.pdf)
* [English, ePub](http://www.phpunit.de/manual/3.7/en/phpunit-book.epub)
* [Brazilian Portuguese, multiple HTML files](http://www.phpunit.de/manual/3.7/pt_br/index.html)
* [Brazilian Portuguese, single HTML file](http://www.phpunit.de/manual/3.7/pt_br/phpunit-book.html)
* [Brazilian Portuguese, PDF](http://www.phpunit.de/manual/3.7/pt_br/phpunit-book.pdf)
* [Brazilian Portuguese, ePub](http://www.phpunit.de/manual/3.7/pt_br/phpunit-book.epub)
* [French, multiple HTML files](http://www.phpunit.de/manual/3.7/fr/index.html)
* [French, single HTML file](http://www.phpunit.de/manual/3.7/fr/phpunit-book.html)
* [French, PDF](http://www.phpunit.de/manual/3.7/fr/phpunit-book.pdf)
* [French, ePub](http://www.phpunit.de/manual/3.7/fr/phpunit-book.epub)
* [Japanese, multiple HTML files](http://www.phpunit.de/manual/3.7/ja/index.html)
* [Japanese, single HTML file](http://www.phpunit.de/manual/3.7/ja/phpunit-book.html)
* [Japanese, PDF](http://www.phpunit.de/manual/3.7/ja/phpunit-book.pdf)
* [Japanese, ePub](http://www.phpunit.de/manual/3.7/ja/phpunit-book.epub)
* [Simplified Chinese, multiple HTML files](http://www.phpunit.de/manual/3.7/zh_cn/index.html)
* [Simplified Chinese, single HTML file](http://www.phpunit.de/manual/3.7/zh_cn/phpunit-book.html)
* [Simplified Chinese, PDF](http://www.phpunit.de/manual/3.7/zh_cn/phpunit-book.pdf)
* [Simplified Chinese, ePub](http://www.phpunit.de/manual/3.7/zh_cn/phpunit-book.epub)

## Mailing Lists

* [dev@phpunit.de](mailto:dev-subscribe@phpunit.de) is a list for those who want to help out with the development of PHPUnit
* [user@phpunit.de](mailto:user-subscribe@phpunit.de) is a list for general PHPUnit support; ask PHPUnit questions here

## IRC

The [#phpunit channel on the Freenode IRC network](irc://irc.freenode.net/phpunit) is a place to chat about PHPUnit.

## List of Contributors

Thanks to everyone who has contributed to PHPUnit! You can find a detailed list of contributors on every PHPUnit related package on GitHub. This list shows only the major components:

* [PHPUnit](https://github.com/sebastianbergmann/phpunit/graphs/contributors)
* [PHP_CodeCoverage](https://github.com/sebastianbergmann/php-code-coverage/graphs/contributors)
* [PHPUnit_MockObject](https://github.com/sebastianbergmann/phpunit-mock-objects/graphs/contributors)

A very special thanks to everyone who has contributed to the documentation and helps maintaining the translations:

* [PHPUnit Documentation](https://github.com/sebastianbergmann/phpunit-documentation/graphs/contributors)

Please refer to [CONTRIBUTING.md](https://github.com/sebastianbergmann/phpunit/blob/master/CONTRIBUTING.md) for information on how to contribute to PHPUnit and its related projects.
