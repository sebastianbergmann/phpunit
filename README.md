# PHPUnit

PHPUnit is the de-facto standard for unit testing in PHP projects. It provides both a framework that makes the writing of tests easy as well as the functionality to easily run the tests and analyse their results.

[![Build Status](https://travis-ci.org/sebastianbergmann/phpunit.png?branch=3.7)](https://travis-ci.org/sebastianbergmann/phpunit)

## Requirements

* PHPUnit 3.7 requires PHP 5.3.3 (or later) but PHP 5.4.6 (or later) is highly recommended.
* [PHP_CodeCoverage](http://github.com/sebastianbergmann/php-code-coverage), the library that is used by PHPUnit to collect and process code coverage information, depends on [Xdebug](http://xdebug.org/) 2.0.5 (or later) but Xdebug 2.2.1 (or later) is highly recommended.

## Installation

There are three supported ways of installing PHPUnit.

You can use the [PEAR Installer](http://pear.php.net/manual/en/guide.users.commandline.cli.php) or [Composer](http://getcomposer.org/) to download and install PHPUnit as well as its dependencies. You can also download a [PHP Archive (PHAR)](http://php.net/phar) of PHPUnit that has all required (as well as some optional) dependencies of PHPUnit bundled in a single file.

### PEAR Installer

The following two commands (which you may have to run as `root`) are all that is required to install PHPUnit using the PEAR Installer:

    pear config-set auto_discover 1
    pear install pear.phpunit.de/PHPUnit

### Composer

To add PHPUnit as a local, per-project dependency to your project, simply add a dependency on `phpunit/phpunit` to your project's `composer.json` file. Here is a minimal example of a `composer.json` file that just defines a development-time dependency on PHPUnit 3.7:

    {
        "require-dev": {
            "phpunit/phpunit": "3.7.*"
        }
    }

### PHP Archive (PHAR)

    wget http://pear.phpunit.de/get/phpunit.phar
    chmod +x phpunit.phar

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
