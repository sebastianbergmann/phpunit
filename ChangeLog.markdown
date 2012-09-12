PHPUnit 3.7
===========

This is the list of changes for the PHPUnit 3.7 release series.

PHPUnit 3.7.0
-------------

* PHPUnit 3.7 is only supported on PHP 5.3.3 (or later) and PHP 5.4.6 (or later) is highly recommended.
* Implemented #200: When using process-isolation don't die silently when unserializing the test result fails.
* Implemented #206: Added a `callback` constraint that is useful for making complex assertions.
* Implemented #207: Restore current working directory if is changed by a test case.
* Implemented #208: Added --test-suffix that allows specifying which filename suffixes are recognised by PHPUnit.
* Implemented #295: `assertArrayHasKey()` and `assertArrayNotHasKey()` now work with objects that implement ArrayAccess.
* Implemented #333: Improved reporting when there are unused CLI arguments to avoid misconceptions.
* Implemented #377: Show messages and stracktraces in JSON output for skipped and incomplete tests.
* Implemented #424: Added `assertJson*` functions that work like the existing `assertXml*` functions.
* Implemented #492: PHPUnit now provides a `configuration.xsd` schema file at [http://schema.phpunit.de/configuration.xsd]() that can be used to validate your `phpunit.xml` and `phpunit.xml.dist` configuration files.
* Implemented #504: Expanded the `@requires` annotation to allow for checking the existence of functions and extensions using multiple `@requires function name` statements.
* Implemented #508 #86: `@expectedExceptionCode` and `@expectedExceptionMessage` can now use constants like `Classname::CONST` as their parameters. They will get evaluated if the class constant exists and used for comparison so test authors can avoid duplication.
* Implemented #512: Test listeners now trigger one autoload call instead of being silently ignored when the class was not loaded.
* Implemented #514: Failed `assertStringMatchesFormat()` calls now produce a better readable diff by only marking lines as different that don't match the format specifiers.
* Implemented #515: Added `assertContainsOnlyInstancesOf()` to help checking Collection objects and arrays with a descriptive assertion.
* Implemented #561: When an `@expectedException` fails it now shows the message of the thrown exception to ease debugging.
* Implemented #586: Improved reporting of exceptions by printing out the previous exception names, messages and traces.
* The `@requires` annotation can now be used on the class DocBlock. Required versions can be overridden in the methods annotation, required functions and extensions will be merged.
* Added `processUncoveredFilesFromWhitelist` configuration setting. When enabled, uncovered whitelisted files are processed to properly calculate the number of executable lines.
* Fixed #322 #320 thanks to #607: Commandline option now override group/exclude settings in phpunit.xml
* Fixed #440: Possible crash when using `--process-isolation` with PHP 5.3 and `detect_unicode=on`.
* Fixed #523: `assertAttributeEquals()` now works with classes extending internal classes like `ArrayIterator`.
* Fixed #581: Generating a diffs could add extra newlines in Windows.
* Fixed #636, #631: Using selenium in combination with autoloaders that die()d or produced errors when a class could't be found caused led to broken tests.
* If no tests where executed, for example because of a `--filter`, PHPUnit now prints a "No tests executed" warning instead of "OK (0 tests...)".
* It is possible again to expect the generic `Exception` class.
* Removed `addUncoveredFilesFromWhitelist` configuration setting.
* Removed deprecated `--skeleton-class` and `--skeleton-test` switches. The functionality is now provided by the `phpunit-skel` command of the `PHPUnit_SkeletonGenerator` package.
* Removed deprecated `PHPUnit_Extensions_OutputTestCase` class.

