PHPUnit 3.5
===========

This is the list of changes for the PHPUnit 3.5 release series.

PHPUnit 3.5.0
-------------

* Implemented TRAC-834: Refactor collection, processing, and rendering of code coverage information using the [PHP_CodeCoverage](http://github.com/sebastianbergmann/php-code-coverage) component.
* Implemented TRAC-948: Add D-BUS test listener.
* Implemented TRAC-967: Only populate whitelist when code coverage is used.
* Implemented TRAC-985: Sort arrays before diff.
* Implemented TRAC-1033: Supplement commandline option `--stop-on-error` and friends.
* Implemented TRAC-1038: Add `assertInstanceOf()`, `assertAttributeInstanceOf()`, `assertNotInstanceOf()`, and `assertAttributeNotInstanceOf()` as well as `assertInternalType()`, `assertAttributeInternalType()`, `assertNotInternalType()`, and `assertAttributeNotInternalType()`.
* Implemented TRAC-1039: Added support for `regexpi:` matcher to Selenium RC driver.
* Added support for mocking/stubbing of static methods. This requires PHP 5.3 and late static binding.
* Added `assertStringMatchesFormat()` and `assertStringNotMatchesFormat()` as well as `assertStringMatchesFormatFile()` and `assertStringNotMatchesFormatFile()` for `EXPECTF`-like (`run-tests.php`) format string matching.
* Added `assertEmpty()` and `assertNotEmpty()` as well as `assertAttributeEmpty()` and `assertAttributeNotEmpty()`.
* Added the `@expectedExceptionCode` and `@expectedExceptionMessage` annotations.
* Added support for the [XML format of mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html#option_mysqldump_xml) to the database extension.
* Added the `<includePath>` element to the `<php>` section of the XML configuration file.
* Added the `verbose` attribute to the `<phpunit>` element of the XML configuration file.
* Added a ticket listener that interacts with the GitHub issue API.
* Added a ticket listener that interacts with the GoogleCode issue API.
* Added a test listener that uses [XHProf](http://mirror.facebook.net/facebook/xhprof/doc.html) to profile the tested code.
* The paths in the XML configuration file can now be relative to the directory that contains the XML configuration file.
* The `@author` annotation is now an alias for `@group` allowing to filter tests based on their authors.
* The `PHPUnit_Extensions_SeleniumTestCase::$autoStop` flag has been removed, please start Selenium RC with `-browserSessionReuse` instead.
* The `--log-metrics` and `--log-pmd` switches have been removed. Their functionality has been or will be merged into [PHP_Depend](http://pdepend.org/) and [PHPMD](http://phpmd.org/). Details can be found [here](http://sebastian-bergmann.de/archives/744-On-PHPUnit-and-Software-Metrics.html).
* The `--ansi` switch has been removed, please use `--colors` instead.
* The `--coverage-source` switch has been removed.
* The `--coverage-xml` switch has been removed, please use `--coverage-clover` instead.
* The `--log-graphviz` switch has been removed.
* The `--log-xml` switch has been removed, please use `--log-junit` instead.
* The `--report` switch has been removed, please use `--coverage-html` instead.
* The `--skeleton` switch has been removed, please use `--skeleton-test` instead.
* The `TestListener` implementation that logs to [PEAR::Log](http://pear.php.net/package/Log) sinks has been removed.
* The test database functionality has been removed.
* The shared fixture functionality has been removed.
* `PHPUnit_Extensions_PerformanceTestCase` has been removed.
* `PHPUnit_Extensions_TicketListener_Trac` has been removed.
* Replaced `PHPUnit_Framework_MockObject` with the [PHP_MockObject](http://github.com/sebastianbergmann/php-mock-objects) component.
* Replaced `PHPUnit_Extensions_Database_TestCase` with the [DbUnit](http://github.com/sebastianbergmann/dbunit) component.
* Replaced `PHPUnit_Extensions_SeleniumTestCase` with the [PHPUnit_Selenium](http://github.com/sebastianbergmann/phpunit-selenium) component.
* Replaced `PHPUnit_Util_FilterIterator` with the [PHP_FileIterator](http://github.com/sebastianbergmann/php-file-iterator) component.
* Replaced `PHPUnit_Util_Template` with the [Text_Template](http://github.com/sebastianbergmann/php-text-template) component.
* Replaced `PHPUnit_Util_Timer` with the [PHP_Timer](http://github.com/sebastianbergmann/php-timer) component.
* Fixed TRAC-1068: `assertSame()` on two floats does not print the error message.
* PHPUnit now requires PHP 5.2.7 (or later) but PHP 5.3.2 (or later) is highly recommended.
