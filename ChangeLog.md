PHPUnit 3.7
===========

This is the list of changes for the PHPUnit 3.7 release series.

PHPUnit 3.7.14
--------------

* Fixed #751: NaN is not equal to NaN now to match PHPs behavior
* Fixed #796 in #799: Mocking a method with a reference to an object made argument matches fail.

PHPUnit 3.7.13
--------------

* Fixed #710: Ensure isolation tests display errors so they can be handled by the test runner.
* Fixed sebastianbergmann/phpunit-mock-objects#81.
* Fixed an issue where PHP complained about an undeclared `$time` variable when running tests in strict mode.

PHPUnit 3.7.12
--------------

* Fixed version number.

PHPUnit 3.7.11
--------------

* Fixed installation issue for Symfony/Yaml.

PHPUnit 3.7.10
--------------

* Fixed #734: `phpunit.phar` cannot be executed if it is renamed.
* Fixed error message when `assertArrayHasKey()` and `assertArrayNotHasKey()` are invoked with wrong arguments.
* Fixed #709: `assertJsonStringEqualsJsonFile` didn't work with json arrays.

PHPUnit 3.7.9
-------------

* Fixed #708: JSON matcher source files missing from `package.xml`.

PHPUnit 3.7.8
-------------

* Fixed #688: Invoke autoloader when checking for `Symfony\Component\Yaml\Dumper`.

PHPUnit 3.7.7
-------------

* Added missing file to PEAR package.

PHPUnit 3.7.6
-------------

* Fixed #682: `phpunit` script appears in stacktrace (when PHPUnit is installed through Composer).

PHPUnit 3.7.5
-------------

* PHPUnit now uses `$_SERVER['SCRIPT_NAME']` instead of `$_SERVER['_']` to filter the `phpunit` script (as the latter is not set when PHPUnit is invoked from Apache Ant's `<exec>` task, for instance).

PHPUnit 3.7.4
-------------

* Fixed #682: `phpunit` script appears in stacktrace.

PHPUnit 3.7.3
-------------

* Improvements to running PHPUnit from a PHAR.

PHPUnit 3.7.2
-------------

* Implemented #656: Always clean up mock objects (and free up memory).
* Implemented #664: Do not rely on autoloader class map to populate blacklist.
* Added the `addUncoveredFilesFromWhitelist` configuration setting back in.
* Fixed #655: Reverted 'More than two positional arguments provided' check as it turned out to be a BC issue.
* Disable token caching (in PHP_TokenStream, used by PHP_CodeCoverage) by default (to reduce memory footprint).

PHPUnit 3.7.1
-------------

* The version number is now displayed when using PHPUnit from a Composer install or Git checkout.

PHPUnit 3.7.0
-------------

* PHPUnit 3.7 is only supported on PHP 5.3.3 (or later) and PHP 5.4.7 (or later) is highly recommended.
* Implemented #200: When using process-isolation don't die silently when unserializing the test result fails.
* Implemented #206: Added a `callback` constraint that is useful for making complex assertions.
* Implemented #207: Restore current working directory if is changed by a test case.
* Implemented #208: Added `--test-suffix` that allows specifying which filename suffixes are recognised by PHPUnit.
* Implemented #295: `assertArrayHasKey()` and `assertArrayNotHasKey()` now work with objects that implement ArrayAccess.
* Implemented #333: Improved reporting when there are unused CLI arguments to avoid misconceptions.
* Implemented #377: Show messages and stracktraces in JSON output for skipped and incomplete tests.
* Implemented #424: Added `assertJson*` functions that work like the existing `assertXml*` functions.
* Implemented #492: PHPUnit now provides a `configuration.xsd` schema file at [http://schema.phpunit.de/configuration.xsd]() that can be used to validate your `phpunit.xml` and `phpunit.xml.dist` configuration files.
* Implemented #495: Added `--testsuite` argument, allowing to filter files/directory by parent testsuite name attribute.
* Implemented #504: Expanded the `@requires` annotation to allow for checking the existence of functions and extensions using multiple `@requires function name` statements.
* Implemented #508 #86: `@expectedExceptionCode` and `@expectedExceptionMessage` can now use constants like `Classname::CONST` as their parameters. They will get evaluated if the class constant exists and used for comparison so test authors can avoid duplication.
* Implemented #512: Test listeners now trigger one autoload call instead of being silently ignored when the class was not loaded.
* Implemented #514: Failed `assertStringMatchesFormat()` calls now produce a better readable diff by only marking lines as different that don't match the format specifiers.
* Implemented #515: Added `assertContainsOnlyInstancesOf()` to help checking Collection objects and arrays with a descriptive assertion.
* Implemented #561: When an `@expectedException` fails it now shows the message of the thrown exception to ease debugging.
* Implemented #586: Improved reporting of exceptions by printing out the previous exception names, messages and traces.
* The `@requires` annotation can now be used on the class DocBlock. Required versions can be overridden in the methods annotation, required functions and extensions will be merged.
* Added `processUncoveredFilesFromWhitelist` configuration setting. When enabled, uncovered whitelisted files are processed to properly calculate the number of executable lines.
* Fixed #322 #320 thanks to #607: Commandline option now override group/exclude settings in `phpunit.xml`
* Fixed #440: Possible crash when using `--process-isolation` with PHP 5.3 and `detect_unicode=on`.
* Fixed #523: `assertAttributeEquals()` now works with classes extending internal classes like `ArrayIterator`.
* Fixed #581: Generating a diffs could add extra newlines in Windows.
* Fixed #636, #631: Using Selenium in combination with autoloaders that `die()` or produce errors when a class cannot be found caused broken tests.
* If no tests where executed, for example because of a `--filter`, PHPUnit now prints a "No tests executed" warning instead of "OK (0 tests...)".
* It is possible again to expect the generic `Exception` class.
* Removed `addUncoveredFilesFromWhitelist` configuration setting.
* Removed deprecated `--skeleton-class` and `--skeleton-test` switches. The functionality is now provided by the `phpunit-skelgen` command of the `PHPUnit_SkeletonGenerator` package.
* Removed deprecated `PHPUnit_Extensions_OutputTestCase` class.

