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

Mailing Lists
-------------

* Send an email to `user-subscribe@phpunit.de` to subscribe to the `phpunit-user` mailing list. This is a medium volume list for general PHPUnit support; ask PHPUnit questions here.
* Send an email to `dev-subscribe@phpunit.de` to subscribe to the `phpunit-dev` mailing list. This is a low volume list for those who want to help out with the development of PHPUnit.

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

The `dbunit`, `php-code-coverage`, `php-file-iterator`, `php-text-template`, `php-timer`, `php-token-stream`, `phpunit`, `phpunit-mock-objects`, and `phpunit-selenium` directories need to be added to the `include_path`.

The `phpunit/phpunit.php` script can be used to invoke the PHPUnit test runner.

The following commands can be used to check out the appropriate branches for PHPUnit 3.5:

    cd phpunit && git checkout 3.5 && cd ..
    cd dbunit && git checkout 1.0 && cd ..
    cd php-file-iterator && git checkout 1.2 && cd ..
    cd php-code-coverage && git checkout 1.0 && cd ..
    cd php-token-stream && git checkout 1.0 && cd ..
    cd phpunit-mock-objects && git checkout 1.0 && cd ..
    cd phpunit-selenium && git checkout 1.0 && cd ..

The following commands can be used to check out the appropriate branches for PHPUnit 3.6:

    cd phpunit && git checkout master && cd ..
    cd dbunit && git checkout master && cd ..
    cd php-file-iterator && git checkout master && cd ..
    cd php-code-coverage && git checkout master && cd ..
    cd php-token-stream && git checkout master && cd ..
    cd phpunit-mock-objects && git checkout master && cd ..
    cd phpunit-selenium && git checkout master && cd ..
