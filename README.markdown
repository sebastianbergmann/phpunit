PHPUnit
=======

PHPUnit is the de-facto standard for unit testing in PHP projects. It provides both a framework that makes the writing of tests easy as well as the functionality to easily run the tests and analyse their results.

Installation
------------

PHPUnit should be installed using the [PEAR Installer](http://pear.php.net/). This installer is the backbone of PEAR, which provides a distribution system for PHP packages, and is shipped with every release of PHP since version 4.3.0.

The PEAR channel (`pear.phpunit.de`) that is used to distribute PHPUnit needs to be registered with the local PEAR environment. Furthermore, components that PHPUnit depends upon are hosted on additional PEAR channels.

    pear channel-discover pear.phpunit.de
    pear channel-discover components.ez.no
    pear channel-discover pear.symfony-project.com

This has to be done only once. Now the PEAR Installer can be used to install packages from the PHPUnit channel:

    pear install phpunit/PHPUnit

After the installation you can find the PHPUnit source files inside your local PEAR directory; the path is usually `/usr/lib/php/PHPUnit`.

Documentation
-------------

The documentation for PHPUnit is available in different formats:

* [English, multiple HTML files](http://www.phpunit.de/manual/3.5/en/index.html)
* [English, single HTML file](http://www.phpunit.de/manual/3.5/en/phpunit-book.html)
* [English, PDF](http://www.phpunit.de/manual/3.5/en/phpunit-book.pdf)
* [English, ePub](http://www.phpunit.de/manual/3.5/en/phpunit-book.epub)
* [Japanese, multiple HTML files](http://www.phpunit.de/manual/3.5/ja/index.html)
* [Japanese, single HTML file](http://www.phpunit.de/manual/3.5/ja/phpunit-book.html)
* [Japanese, PDF](http://www.phpunit.de/manual/3.5/ja/phpunit-book.pdf)
* [Japanese, ePub](http://www.phpunit.de/manual/3.5/ja/phpunit-book.epub)
