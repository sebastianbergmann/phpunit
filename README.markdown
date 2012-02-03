PHPUnit
=======

PHPUnit is the de-facto standard for unit testing in PHP projects. It provides both a framework that makes the writing of tests easy as well as the functionality to easily run the tests and analyse their results.

Requirements
------------

* PHPUnit 3.7 requires PHP 5.3.3 (or later) but PHP 5.4.0 (or later) is highly recommended.
* [PHP_CodeCoverage](http://github.com/sebastianbergmann/php-code-coverage), the library that is used by PHPUnit to collect and process code coverage information, depends on [Xdebug](http://xdebug.org/) 2.0.5 (or later) but Xdebug 2.2.0 (or later) is highly recommended.

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
* [Japanese, multiple HTML files](http://www.phpunit.de/manual/3.7/ja/index.html)
* [Japanese, single HTML file](http://www.phpunit.de/manual/3.7/ja/phpunit-book.html)
* [Japanese, PDF](http://www.phpunit.de/manual/3.7/ja/phpunit-book.pdf)
* [Japanese, ePub](http://www.phpunit.de/manual/3.7/ja/phpunit-book.epub)

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

The `phpunit/phpunit.php` script can be used to invoke the PHPUnit test runner.
