PHPUnit 3.7
===========

This is the list of changes for the PHPUnit 3.7 release series.

PHPUnit 3.7.0
-------------

* Implemented #207: Restore current working directory if is changed by a test case.
* Implemented #333: Improved reporting when there are unused CLI arguments to avoid misconceptions.
* Implemented #377: Show messages and stracktraces in JSON output for skipped and incomplete tests.
* Added `processUncoveredFilesFromWhitelist` configuration setting. When enabled, uncovered whitelisted files are processed to properly calculate the number of executable lines.
* Fixed #440: Possible crash when using `--process-isolation` with PHP 5.3 and `detect_unicode=on`.
* It is possible again to expect the generic `Exception` class.
* Removed `addUncoveredFilesFromWhitelist` configuration setting.
* Removed deprecated `--skeleton-class` and `--skeleton-test` switches. The functionality is now provided by the `phpunit-skel` command of the `PHPUnit_SkeletonGenerator` package.
* Removed deprecated `PHPUnit_Extensions_OutputTestCase` class.
* PHPUnit 3.7 is only supported on PHP 5.3.3 (or later) and PHP 5.4.0 (or later) is highly recommended.
