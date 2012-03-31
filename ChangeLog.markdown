PHPUnit 3.7
===========

This is the list of changes for the PHPUnit 3.7 release series.

PHPUnit 3.7.0
-------------

* Implemented #207: Restore current working directory if is changed by a test case.
* Implemented #208: Added --test-suffix that allows specifying which filename suffixes are recognised by PHPUnit.
* Implemented #295: `assertArrayHasKey()` and `assertArrayNotHasKey()` now work with objects that implement ArrayAccess.
* Implemented #333: Improved reporting when there are unused CLI arguments to avoid misconceptions.
* Implemented #377: Show messages and stracktraces in JSON output for skipped and incomplete tests.
* Implemented #504: Expanded the `@requires` annotation to allow for checking the existence of functions and extensions using multiple `@requires function name` statements.
* The `@requires` annotation can now be used on the class DocBlock. Required versions can be overridden in the methods annotation, required functions and extensions will be merged.
* Implemented #86 #508: `@expectedExceptionCode` and `@expectedExceptionMessage` can now use constants like `Classname::CONST` as their parameters. They will get evaluated if the class constant exists and used for comparison so test authors can avoid duplication.
* Implemented #512: Test listeners now trigger one autoload call instead of being silently ignored when the class was not loaded.
* Added `processUncoveredFilesFromWhitelist` configuration setting. When enabled, uncovered whitelisted files are processed to properly calculate the number of executable lines.
* Fixed #440: Possible crash when using `--process-isolation` with PHP 5.3 and `detect_unicode=on`.
* It is possible again to expect the generic `Exception` class.
* Removed `addUncoveredFilesFromWhitelist` configuration setting.
* Removed deprecated `--skeleton-class` and `--skeleton-test` switches. The functionality is now provided by the `phpunit-skel` command of the `PHPUnit_SkeletonGenerator` package.
* Removed deprecated `PHPUnit_Extensions_OutputTestCase` class.
* PHPUnit 3.7 is only supported on PHP 5.3.3 (or later) and PHP 5.4.0 (or later) is highly recommended.
