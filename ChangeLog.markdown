PHPUnit 3.5
===========

This is the list of changes for the PHPUnit 3.5 release series.

PHPUnit 3.5.14
--------------

* Fixed GH-222: `assertAttribute*()` is too strict.
* Fixed grouping of TestDox messages. Test method names should only be grouped if they are part of a sequence, starting with the initial test method not ending in a number.
* `stream_resolve_include_path()` is now used when available.

PHPUnit 3.5.13
--------------

* The `--debug` switch is now "public" (listed in `--help`).

PHPUnit 3.5.12
--------------

* Fixed GH-14: Weird `RuntimeException` when running test suite with process isolation.
* Fixed GH-27: Process isolation does not work on Windows 7 x64.
* Fixed GH-41: Process isolation on Windows does not work.
* Fixed GH-147: Parse error when using process isolation on Windows.

PHPUnit 3.5.11
--------------

* Fixed GH-127: PHPUnit does not report errors in XML configuration files.
* Fixed an issue with ticket listeners related to tests that use data providers.
* Updated list of dependencies in `package.xml` and added missing runtime checks for optional dependencies.

PHPUnit 3.5.10
--------------

* Fixed GH-71: `PHPUnit_Framework_TestSuite::addTestFile()` has problems identifying the correct test suite.
* Fixed GH-120: Printer class does not handle "file does not exist" problems correctly.
* Fixed GH-125: Work around [PHP bug #47987](http://bugs.php.net/bug.php?id=47987).

PHPUnit 3.5.9
-------------

* Fixed GH-17: Process Isolation breaks for global objects that implement the `Serializable` interface.
* Fixed GH-64: `./` added to path to test when using PHPUnit on Windows terminal.
* Fixed GH-104: Bootstrap must be relative to the current directory.

PHPUnit 3.5.8
-------------

* Fixed GH-84: If no assertions are made the code should not be marked as covered.
* Fixed GH-115: Make most of the attributes in `PHPUnit_Framework_TestCase` private.

PHPUnit 3.5.7
-------------

* Implemented GH-103: Improved handling of deprecated PHPUnit features.
* Fixed GH-100: `assertSame()` does not give useful output on misordered arrays.
* Fixed GH-105: Backup of static attributes causes memory exhaustion.
* The TextUI test runner now prints the normal progress output in verbose mode.

PHPUnit 3.5.6
-------------

* Fixed GH-87: Fatal error when calling `isPublic()` on dynamically created variable.
* Properly marked `assertType()` and `assertNotType()` as well as `assertAttributeType()` and `assertAttributeNotType()` as deprecated. These assertions will removed in PHPUnit 3.6 and should no longer be used. `assertInternalType()` should be used for asserting internal types such as `integer` or `string` whereas `assertInstanceOf()` should be used for asserting that an object is an instance of a specified class or interface.

PHPUnit 3.5.5
-------------

* Added support for `getMockForAbstractClass()` to the mock builder API.

PHPUnit 3.5.4
-------------

* Added a ticket listener that interacts with the Trac issue API.
* Added support for `E_USER_NOTICE` and `E_USER_WARNING` to `PHPUnit_Framework_Error_Notice` and `PHPUnit_Framework_Error_Warning`, respectively.
* Refactored test dependency handling (required for a bugfix in `PHPUnit_Selenium`).
* Fixed `--stop-on-failure`.

PHPUnit 3.5.3
-------------

* Fixed GH-13: Result XML inconsistent when data provider returns empty array or does not exist.
* Fixed the skeleton generator for tested classes.
* Strict mode is now compatible with process isolation.
* Worked around http://bugs.php.net/bug.php?id=52911 to make process isolation work on Windows.

PHPUnit 3.5.2
-------------

* Tests that are incomplete or skipped no longer yield code coverage in strict mode.
* Fixed GH-34: Bogus bootstrap file raises cryptic error.

PHPUnit 3.5.1
-------------

* Fixed GH-30: `--repeat` option does not work.
* Fixed GH-47: Failure message ignored in `assertSelectCount()`.
* Fixed GH-48: Remove strict incomplete duplication.

PHPUnit 3.5.0
-------------

* Implemented TRAC-834: Refactor collection, processing, and rendering of code coverage information using the [PHP_CodeCoverage](http://github.com/sebastianbergmann/php-code-coverage) component.
* Implemented TRAC-948: Add D-BUS test listener.
* Implemented TRAC-967: Only populate whitelist when code coverage is used.
* Implemented TRAC-985: Sort arrays before diff.
* Implemented TRAC-1033: Supplement commandline option `--stop-on-error` and friends.
* Implemented TRAC-1038: Add `assertInstanceOf()`, `assertAttributeInstanceOf()`, `assertNotInstanceOf()`, and `assertAttributeNotInstanceOf()` as well as `assertInternalType()`, `assertAttributeInternalType()`, `assertNotInternalType()`, and `assertAttributeNotInternalType()`.
* Implemented TRAC-1039: Added support for `regexpi:` matcher to Selenium RC driver.
* Implemented TRAC-1078: Added support for setting superglobals via the XML configuration file.
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
* Added the `--strict` switch to mark tests that perform no assertions as incomplete.
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
* The `PHPUnit_Extensions_Story_TestCase` functionality has been deprecated.
* Replaced `PHPUnit_Framework_MockObject` with the [PHPUnit_MockObject](http://github.com/sebastianbergmann/phpunit-mock-objects) component.
* Replaced `PHPUnit_Extensions_Database_TestCase` with the [DbUnit](http://github.com/sebastianbergmann/dbunit) component.
* Replaced `PHPUnit_Extensions_SeleniumTestCase` with the [PHPUnit_Selenium](http://github.com/sebastianbergmann/phpunit-selenium) component.
* Replaced `PHPUnit_Util_FilterIterator` with the [PHP_FileIterator](http://github.com/sebastianbergmann/php-file-iterator) component.
* Replaced `PHPUnit_Util_Template` with the [Text_Template](http://github.com/sebastianbergmann/php-text-template) component.
* Replaced `PHPUnit_Util_Timer` with the [PHP_Timer](http://github.com/sebastianbergmann/php-timer) component.
* Fixed TRAC-1068: `assertSame()` on two floats does not print the error message.
* Fixed GH-7: Code paths that create a `PHPUnit_Framework_Warning` end up serializing/unserializing globals unconditionally.
* PHPUnit now requires PHP 5.2.7 (or later) but PHP 5.3.3 (or later) is highly recommended.
* PHPUnit now uses an autoloader to load its classes. If the tested code requires an autoloader, use `spl_autoload_register()` to register it.
* `PHPUnit/Framework.php` should no longer be included by test code. If needed, include `PHPUnit/Autoload.php` to make PHPUnit's autoloader available.
