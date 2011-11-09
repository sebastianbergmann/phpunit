PHPUnit 3.6
===========

This is the list of changes for the PHPUnit 3.6 release series.

PHPUnit 3.6.3
-------------

* Fixed #386: `<php><env>` in XML configuration file does not call `putenv()`.
* Fixed `--coverage-php` not working from the XML configuration.
* Fixed `--coverage-text` producing a notice in some cases when used from the XML configurtation

PHPUnit 3.6.2
-------------

* Fixed #391: Code Coverage does not work when no XML configuration file is used.

PHPUnit 3.6.1
-------------

* Implemented #395: `--debug` now prints the output of tests for debugging purposes.
* Fixed #394: Backwards compatibility break with regard to comparison of numeric values.
* Fixed `--coverage-php` and `--coverage-text`.

PHPUnit 3.6.0
-------------

* Added `assertCount()` and `assertAttributeCount()` as well as `assertNotCount()` and `assertAttributeNotCount()` to assert the number of elements in an array (or `Countable` or `Iterator` objects).
* Added `assertSameSize()` and `assertNotSameSize()` to assert that the size of two arrays (or `Countable` or `Iterator` objects) is the same.
* Added `returnSelf()` to ease the stubbing and mocking of fluent interfaces.
* Added an option to disable the check for object identity in `assertContains()` and related methods.
* Implemented comparator framework (used by `assertEquals()`, for instance) and improved test failure output.
* Implemented GH-63: Invalid `@covers` annotations should produce a test error instead of aborting PHPUnit.
* Implemented GH-82: Test Skeleton Generator should create `@covers` annotations.
* Implemented GH-83: Test errors and failures as well as incomplete and skipped tests now get coloured letters in the test progress.
* Implemented GH-88: `@expectedException` (and `setExpectedException()`) no longer accept `Exception` as the expected exception class.
* Implemented GH-126: Show used configuration file.
* Implemented GH-189: Add `@requires` annotation to specify the version of PHP and/or PHPUnit required to run a test.
* `assertEquals()` now looks for (and invokes) a `__toString()` method when an object and string are compared.
* `setUpBeforeClass()` and `tearDownAfterClass()` are no longer invoked when all tests of the class are skipped.
* Using the `@small` (alias for `@group small`), `@medium` (alias for `@group medium`), and `@large` (alias for `@group large`) annotations, a test can now be marked to be of a certain size. By default, a test is "small".
* A test must not `@depend` on a test that is larger than itself.
* In strict mode, the execution of a small test is (by default) aborted after 1 second (when the `PHP_Invoker` package is installed and the `pcntl` extension is available).
* In strict mode, the execution of a medium test is (by default) aborted after 10 seconds (when the `PHP_Invoker` package is installed and the `pcntl` extension is available).
* In strict mode, the execution of a large test is (by default) aborted after 60 seconds (when the `PHP_Invoker` package is installed and the `pcntl` extension is available).
* In strict mode, a test must not print any output.
* Any output made by a test is now "swallowed".
* `@ticket` is now an alias for `@group`.
* Added `--printer` to specify a class (that extends `PHPUnit_Util_Printer` and implements `PHPUnit_Framework_TestListener`) to print test runner output.
* Added `-h` as alias for `--help` and `-c` as alias for `--configuration`.
* Added an option to disable the caching of `PHP_Token_Stream` objects during code coverage report generation to reduce the memory usage.
* `assertType()` and `assertNotType()` as well as `assertAttributeType()` and `assertAttributeNotType()` have been removed. `assertInternalType()` should be used for asserting internal types such as `integer` or `string` whereas `assertInstanceOf()` should be used for asserting that an object is an instance of a specified class or interface.
* The `PHPUnit_Extensions_OutputTestCase` functionality has been merged into `PHPUnit_Framework_TestCase`.
* The `PHPUnit_Extensions_Story_TestCase` functionality has been moved to a separate package (`PHPUnit_Story`).
* The `PHPUnit_Util_Log_DBUS` functionality has been moved to a separate package (`PHPUnit_TestListener_DBUS`).
* The `PHPUnit_Util_Log_XHProf` functionality has been moved to a separate package (`PHPUnit_TestListener_XHProf`).
* The `--wait` functionality has been removed.
* The syntax check functionality has been removed.
* The XML configuration file is now the only way to configure the blacklist and whitelist for code coverage reporting.
