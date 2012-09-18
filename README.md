PHPUnit
=======

PHPUnit is the de-facto standard for unit testing in PHP projects. It provides both a framework that makes the writing of tests easy as well as the functionality to easily run the tests and analyse their results.

Requirements
------------

* PHPUnit 3.7 requires PHP 5.3.3 (or later) but PHP 5.4.6 (or later) is highly recommended.
* [PHP_CodeCoverage](http://github.com/sebastianbergmann/php-code-coverage), the library that is used by PHPUnit to collect and process code coverage information, depends on [Xdebug](http://xdebug.org/) 2.0.5 (or later) but Xdebug 2.2.1 (or later) is highly recommended.

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

* [English, multiple HTML files](http://www.phpunit.de/manual/3.7/en/index.html)
* [English, single HTML file](http://www.phpunit.de/manual/3.7/en/phpunit-book.html)
* [English, PDF](http://www.phpunit.de/manual/3.7/en/phpunit-book.pdf)
* [English, ePub](http://www.phpunit.de/manual/3.7/en/phpunit-book.epub)
* [French, multiple HTML files](http://www.phpunit.de/manual/3.7/fr/index.html)
* [French, single HTML file](http://www.phpunit.de/manual/3.7/fr/phpunit-book.html)
* [French, PDF](http://www.phpunit.de/manual/3.7/fr/phpunit-book.pdf)
* [French, ePub](http://www.phpunit.de/manual/3.7/fr/phpunit-book.epub)
* [Japanese, multiple HTML files](http://www.phpunit.de/manual/3.7/ja/index.html)
* [Japanese, single HTML file](http://www.phpunit.de/manual/3.7/ja/phpunit-book.html)
* [Japanese, PDF](http://www.phpunit.de/manual/3.7/ja/phpunit-book.pdf)
* [Japanese, ePub](http://www.phpunit.de/manual/3.7/ja/phpunit-book.epub)

IRC
---

The [#phpunit channel on the Freenode IRC network](irc://irc.freenode.net/phpunit) is a place to chat about PHPUnit.

List of Contributors
--------------------

Thanks to everyone who has contributed to PHPUnit! You can find a detailed list of contributors on every PHPUnit related package on GitHub. This list shows only the major components:

- [PHPUnit](https://github.com/sebastianbergmann/phpunit/graphs/contributors)
- [PHP_CodeCoverage](https://github.com/sebastianbergmann/php-code-coverage/graphs/contributors)
- [PHPUnit_MockObject](https://github.com/sebastianbergmann/phpunit-mock-objects/graphs/contributors)

A very special thanks to everyone who has contributed to the documentation and helps maintaining the translations:

- [PHPUnit Documentation](https://github.com/sebastianbergmann/phpunit-documentation/graphs/contributors)

Please refer to [CONTRIBUTING.md](https://github.com/sebastianbergmann/phpunit/blob/master/CONTRIBUTING.md) for information on how to contribute to PHPUnit and its related projects.
