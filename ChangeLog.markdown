PHPUnit 3.6
===========

This is the list of changes for the PHPUnit 3.6 release series.

PHPUnit 3.6.11
--------------

* Fixed #510: PHP 5.4 `callable` type hint raises `E_NOTICE` when object is mocked.
* Fixed phpunit-selenium #72: Allow Selenium to have tests that have no concrete test methods.
* Fixed phpunit-mock-object #83: `getMockFromWsdl()` didn't work twice with the same wsdl file.
* Fixed #503: Improved the error message if compared strings only differ in line ending style.

PHPUnit 3.6.10
--------------

* Tests for which the execution is aborted due to a timeout are no longer shown as incomplete but as an error instead.
* Fixed the fix for #466.

PHPUnit 3.6.9
-------------

* Fixed #466: Undefined offset in `Configuration.php`.

PHPUnit 3.6.8
-------------

* Fixed #463: `PHPUnit_Framework_TestCase::testRequirements()` collides with actual test methods.
* Fixed reflection errors when `PHPUnit_Framework_Warning` is used.
* Marked `--skeleton-class` and `--skeleton-test` as deprecated. Please use the `phpunit-skelgen` tool that is provided by the `PHPUnit_SkeletonGenerator` package instead.

PHPUnit 3.6.7
-------------

* Fixed #452: Regression when using (deprecated) `AllTests.php` approach to organize test suites.

PHPUnit 3.6.6
-------------

* Improved exception message in `PHPUnit_Framework_TestSuite::__construct()`.
* Improved failure messages for exception expectations.
* `@expectedExceptionCode` may now be 0.
* Test output now is included as an `output` element in the JSON logfile.
* Fixed #445: Assertions on output did not work in strict mode.
* Fixed stacktraces on Windows wrongly showing the PHPUnit files.

PHPUnit 3.6.5
-------------

* Implemented #406: Improved the failure description for `assertStringMatchesFormat*()`.
* Fixed #204: Bootstrap script should be loaded before trying to load `testSuiteLoaderClass`.
* Fixed #413: PHPT test failures display double diffs.
* Fixed #420: Using the `@outputBuffering enabled` annotation leads to failing tests when an output string was expected.
* Fixed #430: `OutputTestCase` did not work with `@depends`. Please note that this way of output testing is still deprecated.
* Fixed #432: Process Isolation did not work when PHPUnit is invoked through Apache Ant, for instance, due to PHP binary detection issues.
* Fixed #433: Testing output always printed the output during test execution.

PHPUnit 3.6.4
-------------

* Fixed #244: `@expectedExceptionCode` may now be a string.
* Fixed #264: XML test suite configuration using `<file>` tags failed when PHPUnit was run from another directory.
* Fixed #306: Assertions with binary data caused problems. Strings with non-printable characters will now be shown in hexadecimal representation.
* Fixed #328: Parsing of one line annotations did not work.
* Fixed #407: `$_SERVER['_']` was not utilized properly to specify the PHP interpreter used for process isolation.
* Fixed #411: Do not swallow output printed from test(ed) code by default.

PHPUnit 3.6.3
-------------

* Fixed #386: `<php><env>` in XML configuration file does not call `putenv()`.
* Fixed `--coverage-php` not working from the XML configuration.
* Fixed `--coverage-text` producing a notice in some cases when used from the XML configuration

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
* Implemented #63: Invalid `@covers` annotations should produce a test error instead of aborting PHPUnit.
* Implemented #82: Test Skeleton Generator should create `@covers` annotations.
* Implemented #83: Test errors and failures as well as incomplete and skipped tests now get coloured letters in the test progress.
* Implemented #88: `@expectedException` (and `setExpectedException()`) no longer accept `Exception` as the expected exception class.
* Implemented #126: Show used configuration file.
* Implemented #189: Add `@requires` annotation to specify the version of PHP and/or PHPUnit required to run a test.
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
