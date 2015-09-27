# Changes in PHPUnit 5.0

All notable changes of the PHPUnit 5.0 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [5.0.0] - 2015-10-02

### Added

* Implemented [#1604](https://github.com/sebastianbergmann/phpunit/issues/1604): A `@small` test should be marked as risky when it executes code that performs I/O operations
* Implemented [#1656](https://github.com/sebastianbergmann/phpunit/issues/1656): Allow sorting test failures in reverse
* Merged [#1753](https://github.com/sebastianbergmann/phpunit/issues/1753): Added the `assertFinite()`, `assertInfinite()` and `assertNan()` assertions
* Merged [#1876](https://github.com/sebastianbergmann/phpunit/issues/1876): Added the `--atleast-version` commandline option
* Implemented [#1780](https://github.com/sebastianbergmann/phpunit/issues/1780): Support for deep-cloning of results passed between tests using `@depends`
* Implemented [#1821](https://github.com/sebastianbergmann/phpunit/issues/1821): Expectations on mock objects passed via `@depends` are now also evaluated for the depending test
* Added `--whitelist` commandline option to configure a whitelist for code coverage analysis
* Added convenience wrapper `getMockWithoutInvokingTheOriginalConstructor()` to create a test double without invoking the original class' constructor
* Added TeamCity test result logger for more seamless integration of PHPUnit with PhpStorm

### Changed

* Merged [#1781](https://github.com/sebastianbergmann/phpunit/issues/1781): Empty string is not treated as a valid JSON string anymore
* Merged [#1822](https://github.com/sebastianbergmann/phpunit/issues/1822): Always output progress totals on last line
* It is now mandatory to configure a whitelist for code coverage analysis
* Renamed the `beStrictAboutTestSize` configuration option to `enforceTimeLimit`
* Printer-related CLI options now override printer-related configuration settings

### Removed

* The `assertSelectCount()`, `assertSelectRegExp()`, `assertSelectEquals()`, `assertTag()`, `assertNotTag()` assertions have been removed
* The `--strict` commandline option and the XML configuration's `strict` attribute have been removed
* The code coverage blacklist functionality has been removed
* The PHPUnit_Selenium component is no longer bundled in the PHAR distribution
* The PHPUnit_Selenium component can no longer be configured using the `<selenium/browser>` element of PHPUnit's configuration file
* PHPUnit is no longer supported on PHP 5.3, PHP 5.4, and PHP 5.5

[5.0.0]: https://github.com/sebastianbergmann/phpunit/compare/4.8...5.0.0

