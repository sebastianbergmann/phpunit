PHPUnit 3.4
===========

This is the list of changes for the PHPUnit 3.4 release series.

PHPUnit 3.4.13
--------------

* Fixed TRAC-1035: PostgreSQL MetaData queries are incorrect.
* The `--repeat` switch is no longer deprecated.

PHPUnit 3.4.12
--------------

* Implemented TRAC-1027: Declare a decent return-type in `PHPUnit_Framework_TestCase::getMock()`.
* Fixed TRAC-1013: `Undefined index: _ENV` running supplied tests.
* Fixed TRAC-1016: Usage of `{}` to access string offsets is deprecated.
* Fixed TRAC-1021: Depending on a test that uses a data provider does not work.
* Fixed TRAC-1030: Selenese tests cause double escaping of Selenium actions and arguments.

PHPUnit 3.4.11
--------------

* Fixed TRAC-952: `tearDownAfterClass()` irregularly called with `@dataProvider`.
* Fixed TRAC-997: Missing include of `PHPUnit_Framework_Exception` in `PHPUnit/Util/Filter.php`.

PHPUnit 3.4.10
--------------

* Invalid data provider specifications are now handled gracefully.
* Fixed TRAC-993: Incorrect test on `$reflector->getFileName()`.
* Fixed TRAC-994: Cannot use multibyte characters in the provider method name.
* Made `PHPUnit_Framework_Assert::readAttribute()` compatible with PHP 5.1.6.
* Made `PHPUnit_Extensions_Database_Operation_RowBased` compatible with PHP 5.1.6.

PHPUnit 3.4.9
-------------

* Removed usage of `SplFileInfo::getRealPath()` which is not available in PHP 5.1.6.

PHPUnit 3.4.8
-------------

* Made dependency on YAML component optional.

PHPUnit 3.4.7
-------------

* Fixed TRAC-979: `@codeCoverageIgnoreStart` is being ignored.
* Fixed TRAC-986: Memory leaks with mock objects.
* Reduced memory footprint of code coverage report generation.

PHPUnit 3.4.6
-------------

* `assertContains()` and `assertNotContains()` no longer use `===` for non-objects.
* Implemented TRAC-968: Support `scalar` in `assertType()`.
* Fixed TRAC-779: `PHPUnit_Util_Getopt::parseLongOption()` causes problems with custom error handlers.
* Fixed TRAC-892: Oracle's `TRUNCATE` requires additional `TABLE` statement.
* Fixed TRAC-942: Process Isolation should define constants before including files.
* Fixed TRAC-943: `xdebug.show_exception_trace=On` breaks PHPUnit.
* Fixed TRAC-944: Closures might trick the metrics analyzer.
* Fixed TRAC-945: File not found error (and other weird stuff) comes up if shell sends `\n` in `argv`.
* Fixed TRAC-949: Invalid XML generated on binary diffs.
* Fixed TRAC-969: Destructors cannot be mocked.
* Fixed TRAC-977: `getMockForAbstractClass()` mocks all methods on an abstract class without abstract methods.
* The [YAML](http://components.symfony-project.org/yaml/) component from the [Symfony Components](http://components.symfony-project.org/) is no longer bundled but required as a dependency.

PHPUnit 3.4.5
-------------

* The TextUI test runner now prints the peak of memory allocated by PHP (when `memory_get_peak_usage()` is available).
* Fixed TRAC-954: `PHPUnit_Util_ErrorHandler::handleError()` pushes error to stack even if not wanted to reported.
* Fixed a bug where files were ignored if the directory path contains `/../`.

PHPUnit 3.4.3
-------------

* Implemented TRAC-941: Add fallback for `array_fill_keys()`.
* Fixed TRAC-920: `PHPUnit_Extensions_Database_DB_MetaData_Oci` throws an undefined variable.
* Fixed TRAC-923: `SeleniumTestCase` sometimes leaves browser windows open.
* Fixed TRAC-924: Selenium driver misses `useXpathLibrary`.
* Fixed TRAC-932: Process isolation incompatible with Zend Framework.
* Fixed TRAC-933: Objects in global state need `__set_state()`.
* Fixed TRAC-938: Isolated test running sets include_path too late.
* Fixed `tearDownAfterClass()` getting called more than once.
* Updated bundled YUI to version 2.8.0r4.

PHPUnit 3.4.2
-------------

* Fixed TRAC-889: `--skeleton-class` does not work with `@depends` annotation.
* Fixed TRAC-902: `PHPUnit_Util_File::getClassesInFile()` does not handle nested namespaces correctly.
* Fixed TRAC-905: Files with no methods or classes show incorrect code coverage with `--coverage-clover`.
* Fixed TRAC-909: Stubbing a web service with `getMockFromWsdl()` throws a fatal error.
* Fixed TRAC-918: Truncate operation throws an error.
* The backup and restore operations for static attributes has been disabled by default.

PHPUnit 3.4.1
-------------

* Fixed TRAC-874: Typo in a `PHPUnit_Framework_TestCase` exception.
* Fixed TRAC-875: `@runInSeparateProcess` and `@runTestsInSeparateProcesses` do not work with data providers.
* Fixed TRAC-876: `@preserveGlobalState` does not work with data providers.
* Fixed TRAC-877: Fatal error in `PHPUnit_Util_TestDox_ResultPrinter`.
* Fixed TRAC-879: Execution of PHPUnit throws `ReflectionException`.
* Fixed TRAC-884: `PHPUnit_Util_File` fails on classes containing strings with curly syntax.
* Fixed TRAC-887: `PHPUnit_Util_File` parses classes with PHP 4 style constructors incorrectly.
* Fixed TRAC-890: PHPUnit skips every test with `@depends` on Windows.
* Fixed TRAC-893: `--group` and `--exclude-group` seem to be not working.
* Fixed TRAC-895: `@covers` not working, says class does not exist.

PHPUnit 3.4.0
-------------

* Improvements and Fixes for `PHPUnit_Framework_TestCase`
  * Added the `@depends [class::]method` annotation to express that a test depends on another test. If `[class::]method()` has not passed before the annotated test, the annotated test will be skipped.
  * Implemented TRAC-144: Added the `@runTestsInSeparateProcesses` and `@runInSeparateProcess` annotations to selectively run tests in separate PHP processes.
  * Implemented TRAC-814: Added the `setUpBeforeClass()` and `tearDownAfterClass()` template methods.
  * Added the `@errorHandler` annotation to control PHPUnit's error handler.
  * Added the `@outputBuffering` annotation to control PHP's output buffering.
  * Improvements and Fixes for the Mock Objects System
    * Implemented TRAC-638: Support mocking SOAP webservices based on WSDL specification.
    * Implemented TRAC-694: Added `getMockForAbstractClass()` that returns a mock object for the specified abstract class with all abstract methods of the class mocked and concrete methods not mocked.
    * Implemented TRAC-756: Handle exceptions in `setUp()` and `tearDown()`.
    * Fixed TRAC-545: Cannot create mock object when not calling a contructor and a class is implementing an interface.
    * Fixed TRAC-630: Mock generator fails to generate mock for a class with `__clone()` method marked as `final`.
    * Fixed TRAC-755: Case sensitivity issue with invocation mocker and return values.
    * Fixed TRAC-813: Mock Objects and `with()` do not work together.
    * Fixed TRAC-822: Mocking namespaced class results in autoloading the wrong class.
  * Added a blacklist for global variables that should be excluded from the backup/restore operation.
  * Implemented backup/restore for static attributes in user-defined classes (requires PHP 5.3). This is enabled by default for consistency with the backup/restore for global variables.
  * Added support for global configuration of backup/restore operations for global variables and static attributes in user-defined classes, both via TextUI switches and XML configuration file directives.
  * Add support for `@backupGlobals` and `@backupStaticAttributes` annotations to control the backup/restore operations for global variables and static attributes in user-defined classes, both on the test class level and the test method level.
  * Fixed TRAC-686: `assertTag()` only works for lowercase tags.
  * Fixed TRAC-817: `assert*Contains*()` does not handle `IteratorAggregate` implementors.
  * The backwards compatiblity layer for `PHPUnit_Framework_TestCase::sharedAssertions()` has been removed. Please migrate to `PHPUnit_Framework_TestCase::assertPostConditions()`.
* Improvements and Fixes for `PHPUnit_Extensions_SeleniumTestCase`
  * Implemented TRAC-605: Make directory for Selenium code coverage files configurable.
  * Implemented TRAC-631 and #769: Support more Selenium commands.
    * Please note that the following commands had to be renamed:
      * `assertAlertPresent()` has been renamed to `assertAlert()`
      * `assertNoAlertPresent()` has been renamed to `assertNotAlert()`
      * `assertNoConfirmationPresent()` has been renamed to `assertConfirmationNotPresent()`
      * `assertLocationEquals() has been renamed to `assertLocation()`
      * `assertLocationNotEquals()` has been renamed to `assertNotLocation()`
      * `assertNoPromptPresent()` has been renamed to `assertPromptNotPresent()`
      * `assertNothingSelected()` has been renamed to `assertNotSomethingSelected()`
      * `assertTitleEquals()` has been renamed to `assertTitle()`
      * `assertTitleNotEquals()` has been renamed to `assertNotTitle()`
  * Implemented TRAC-637: Enable PHPUnit to recognize `exact:`, `glob:`, and `regex:` matching within the use of Selenium `assert*()` and `verify*()` commands.
  * Implemented TRAC-655: Allow `phpunit_coverage.php` to be symlinked.
  * Implemented TRAC-658: Selenium driver times out during `fopen()`.
  * Implemented TRAC-682: Automatically add URL to error messages in Selenium test cases.
  * A screenshot can now optionally be captured when a test fails.
  * Code Coverage statistics on the function, method, and class level now work correctly for Selenium tests.
  * Fixed TRAC-698: `PHPUnit_Extensions_SeleniumTestCase::assertElementContainsText()` checks value and not text.
  * Fixed TRAC-706: Selenium error message is ambiguous.
  * Fixed TRAC-746: `assertTextPresent()` is not 100% compatible with Selenium IDE.
  * Fixed TRAC-749: Selenium timeout used as HTTP connection timeout.
    * `PHPUnit_Extensions_SeleniumTestCase::setTimeout()` now expects its argument in seconds instead of milliseconds.
    * `PHPUnit_Extensions_SeleniumTestCase::setHttpTimeout()` was added to control the timeout for the HTTP connection to the Selenium RC server. 
  * Fixed TRAC-832: Class `PHPUnit_Extensions_SeleniumTestCase` could not be found.
* Improvements and Fixes for PHPUnit_Extensions_DatabaseTestCase
  * Added the `dbunit` command-line utility to import/export/convert data sets.
  * Implemented TRAC-526: More flexible data set filtering.
  * Implemented TRAC-548: Add schema support for flat XML format.
  * Implemented TRAC-604: Add YAML DataSet support for DbUnit.
  * Implemented TRAC-664: Column names are not escaped correctly.
* Improvements, Fixes, and Changes for the TextUI Test Runner
  * Implemented TRAC-144: Added the `--process-isolation` switch and the `processIsolation="true"` XML configuration file setting to run each test in a separate PHP process.
  * Added YAML diagnostics block to TAP output.
  * `TestListener`s can now be configured via the XML configuration file.
  * Implemented TRAC-9: Prepend PHP's `include_path` with given path(s).
  * Implemented TRAC-598: Make `TestSuiteLoader` configurable via XML configuration file.
  * Implemented TRAC-672: Added `--stderr` switch to optionally print to `STDERR` instead of `STDOUT`.
  * Implemented TRAC-730: Let `PHPUnit_TextUI_TestRunner` accept `PHPUnit_Util_Configuration` objects.
  * Implemented TRAC-766: Allow namespaced test suite loader class name to path conversion.
  * Implemented TRAC-794: Ignore test file names that are prefixed with `.`.
  * Deprecated switches
    * The `--ansi` switch has been deprecated, please use `--colors` instead.
    * The `--coverage-xml` switch has been deprecated, please use `--coverage-clover` instead.
    * The `--log-graphviz` switch has been deprecated.
    * The `--log-metrics` and `--log-pmd` switches have been deprecated.
    * The `--log-xml` switch has been deprecated, please use `--log-junit` instead.
    * The `--repeat` switch has been deprecated.
    * The `--report` switch has been deprecated, please use `--coverage-html` instead.
    * The `--skeleton` switch has been deprecated, please use `--skeleton-test` instead.
    * The syntax check functionality has been disabled by default.
    * The test database functionality has been deprecated.
  * Fixed TRAC-796: PHPUnit tries to run abstract testcases.
  * Fixed TRAC-824: Faulty output on console when using `--testdox`.
* Improvements and Fixes for the Code Coverage reporting
  * The `@covers` annotation is now supported on the `setUp()`, `assertPreConditions()`, `assertPostConditions()`, and `tearDown()` template methods of `PHPUnit_Framework_TestCase`.
  * Code Coverage statistics on the function, method, and class level are now calculated in way that makes more sense.
    * A method is marked as covered when all of its executable lines are executed by at least one test.
    * A class is marked as covered when all of its methods are fully covered. 
  * Code Coverage statistics on the function, method, and class level now work correctly for Selenium tests.
  * Implemented TRAC-705: Added method name information to Clover XML code coverage logfile.
  * Fixed TRAC-582: PHPUnit reports more covered statements than statements in class.
  * Fixed TRAC-615: Tests are not written to test database when using whitelist.
* Added `PHPUnit_Framework_Assert::assertStringStartsWith()` and `PHPUnit_Framework_Assert::assertStringEndsWith()` as well as `PHPUnit_Framework_Assert::stringStartsWith()` and `PHPUnit_Framework_Assert::stringEndsWith()`.
* Added `PHPUnit_Extensions_TicketListener_Trac` for automatically closing and reopening Trac tickets based on test results.
* Implemented an annotation, `@testdox`, to specify the output for a test when using `--testdox`.
* Implemented TRAC-597: Add prefix configuration for test files.
* Implemented TRAC-616: Test Database does not contain full filenames.
* Implemented TRAC-642: Test Database Nested Set representation is way to slow.
* Implemented TRAC-673: Allow constants in PHP section of XML configuration file.
* `PHPUnit_Util_Configuration` is now a singleton.
* The `PHPUnit_Util_Class::getClassesInFile()` and `PHPUnit_Util_Class::getFunctionsInFile()` methods have been renamed to `PHPUnit_Util_File::getClassesInFile()` and `PHPUnit_Util_File::getFunctionsInFile()`, respectively.
* The signature of `PHPUnit_Util_File::getClassesInFile()`, `PHPUnit_Util_File::getFunctionsInFile()`, and `PHPUnit_Util_Class::getPackageInformation()` has changed. These methods also no longer use the Reflection API.
* The `PHPUnit_Util_Class::getFunctionSignature()` and `PHPUnit_Util_Class::getMethodSignature()` methods have been dropped.
* Renamed `PHPUnit_Framework_Assert::getStaticAttribute()` and `PHPUnit_Framework_Assert::getObjectAttribute()` to `PHPUnit_Util_Class::getStaticAttribute()` and `PHPUnit_Util_Class::getObjectAttribute()`, respectively.
* Fixed TRAC-764: Argument Type Mismatch in `BaseTestRunner`.
* PHPUnit 3.4 should still work with PHP 5.1.4 (or later) but PHP 5.3.0 (or later) is highly recommended.

