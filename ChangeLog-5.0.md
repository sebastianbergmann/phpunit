# Changes in PHPUnit 5.0

All notable changes of the PHPUnit 5.0 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [5.0.10] - 2015-11-30

### Fixed

* Fixed [#1953](https://github.com/sebastianbergmann/phpunit/issues/1953): `Error`s raised outside the scope of a test method are not handled properly
* Fixed [#1955](https://github.com/sebastianbergmann/phpunit/issues/1955): Process isolation fails when running tests with `phpdbg -qrr`

## [5.0.9] - 2015-11-10

### Added

* Merged [#1909](https://github.com/sebastianbergmann/phpunit/issues/1909): Added `flowId` parameter to each TeamCity message (for parallel tests)

### Fixed

* Fixed [#1935](https://github.com/sebastianbergmann/phpunit/issues/1935): `PHP_CodeCoverage_Exception` not handled properly
* Fixed [#1944](https://github.com/sebastianbergmann/phpunit/issues/1944): Exceptions are not handled correctly on PHP 7 when an exception is expected
* Fixed [#1948](https://github.com/sebastianbergmann/phpunit/issues/1948): Unable to use PHAR due to unsupported signature error

### Removed

* Removed leftover references to PHPUnit_Selenium

## [5.0.8] - 2015-10-23

### Added

* Implemented [#1925](https://github.com/sebastianbergmann/phpunit/issues/1925): Provide a library-only PHAR

## [5.0.7] - 2015-10-22

### Fixed

* The backup of global state is now properly restored when changes to global state are disallowed
* The `__PHPUNIT_PHAR__` constant is now properly set when the PHPUnit PHAR is used as a library

## [5.0.6] - 2015-10-14

### Added

* Added the `--self-upgrade` commandline switch for upgrading a PHPUnit PHAR to the latest version

### Changed

* The `--self-update` commandline switch now updates a PHPUnit PHAR to the latest version within the same release series

### Fixed

* Fixed [#1892](https://github.com/sebastianbergmann/phpunit/issues/1892): `--coverage-text` does not honor color settings

## [5.0.5] - 2015-10-12

### Changed

* Merged [#1893](https://github.com/sebastianbergmann/phpunit/issues/1893): Removed workaround for phpab bug

## [5.0.4] - 2015-10-07

### Fixed

* Fixed [#1857](https://github.com/sebastianbergmann/phpunit/issues/1857): `@covers` and `@uses` should only take a single word
* Fixed [#1898](https://github.com/sebastianbergmann/phpunit/issues/1898): `@covers` and `@uses` cannot be used for namespaced functions

## [5.0.3] - 2015-10-02

* Fixed check for PHP version in PHAR distribution of PHPUnit

## [5.0.2] - 2015-10-02

### Fixed

* Fixed [#1879](https://github.com/sebastianbergmann/phpunit/issues/1879): `assertEqualXMLStructure()` cannot compare nodes with an ID
* Fixed [#1887](https://github.com/sebastianbergmann/phpunit/issues/1887): PHAR distribution of PHPUnit 5.0.1 does not work

## [5.0.1] - 2015-10-02

### Fixed

* Merged [#1885](https://github.com/sebastianbergmann/phpunit/issues/1885): Fixed handling of PHP configuration settings for process isolation
* An outdated version of DbUnit was bundled in the PHAR distribution of PHPUnit

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

[5.0.10]: https://github.com/sebastianbergmann/phpunit/compare/5.0.9...5.0.10
[5.0.9]: https://github.com/sebastianbergmann/phpunit/compare/5.0.8...5.0.9
[5.0.8]: https://github.com/sebastianbergmann/phpunit/compare/5.0.7...5.0.8
[5.0.7]: https://github.com/sebastianbergmann/phpunit/compare/5.0.6...5.0.7
[5.0.6]: https://github.com/sebastianbergmann/phpunit/compare/5.0.5...5.0.6
[5.0.5]: https://github.com/sebastianbergmann/phpunit/compare/5.0.4...5.0.5
[5.0.4]: https://github.com/sebastianbergmann/phpunit/compare/5.0.3...5.0.4
[5.0.3]: https://github.com/sebastianbergmann/phpunit/compare/5.0.2...5.0.3
[5.0.2]: https://github.com/sebastianbergmann/phpunit/compare/5.0.1...5.0.2
[5.0.1]: https://github.com/sebastianbergmann/phpunit/compare/5.0.0...5.0.1
[5.0.0]: https://github.com/sebastianbergmann/phpunit/compare/4.8...5.0.0

