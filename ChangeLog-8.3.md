# Changes in PHPUnit 8.3

All notable changes of the PHPUnit 8.3 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [8.3.4] - 2019-08-11

### Changed

* Implemented [#3788](https://github.com/sebastianbergmann/phpunit/pull/3788): Cast exception message to string

### Fixed

* Fixed [#3772](https://github.com/sebastianbergmann/phpunit/issues/3772): Process Isolation does not work when PHPDBG is used

## [8.3.3] - 2019-08-03

### Fixed

* Fixed [#3781](https://github.com/sebastianbergmann/phpunit/pull/3781): `MockBuilder::addMethods()` and `MockBuilder::onlyMethods()` do not handle empty parameter array correctly

## [8.3.2] - 2019-08-02

### Fixed

* More work on the fix for [#3774](https://github.com/sebastianbergmann/phpunit/issues/3774): Restored name of `PHPUnit\Framework\Error\Deprecated`

## [8.3.1] - 2019-08-02

### Fixed

* Fixed [#3774](https://github.com/sebastianbergmann/phpunit/issues/3774): PHP errors, notices, etc. cannot be tested anymore with PHPUnit 8.3

## [8.3.0] - 2019-08-02

### Added

* Implemented [#3687](https://github.com/sebastianbergmann/phpunit/pull/3687): Introduce `MockBuilder::addMethods()` and `MockBuilder::onlyMethods()` as alternatives to `MockBuilder::setMethods()`
* Implemented [#3741](https://github.com/sebastianbergmann/phpunit/issues/3741): Format class names as well as method names in TestDox output
* Implemented [#3748](https://github.com/sebastianbergmann/phpunit/issues/3748): Add option to sort tests based on information from `@small`, `@medium`, and `@large`
* Added `TestCase::getActualOutputForAssertion()` as a wrapper for `TestCase::getActualOutput()` to prevent a test being marked as risky when it prints output and that output is not expected using `TestCase::expectOutputString()` or `TestCase::expectOutputRegEx()`

### Changed

* Implemented [#2015](https://github.com/sebastianbergmann/phpunit/issues/2015): Prefix all code bundled in PHAR distribution with random/unique namespace
* Implemented [#3503](https://github.com/sebastianbergmann/phpunit/issues/3503): The error handler has been refactored to not rely on global state
* Implemented [#3521](https://github.com/sebastianbergmann/phpunit/issues/3521): The `@errorHandler` annotation, which controlled a feature that was not documented and did not work correctly, does not have an effect anymore
* Implemented [#3522](https://github.com/sebastianbergmann/phpunit/issues/3522): The `TestCase::setUseErrorHandler()` method, which controlled a feature that was not documented and did not work correctly, has been deprecated and does not have an effect anymore
* Implemented [#3687](https://github.com/sebastianbergmann/phpunit/pull/3687): `MockBuilder::setMethods()` is now deprecated

[8.3.4]: https://github.com/sebastianbergmann/phpunit/compare/8.3.3...8.3.4
[8.3.3]: https://github.com/sebastianbergmann/phpunit/compare/8.3.2...8.3.3
[8.3.2]: https://github.com/sebastianbergmann/phpunit/compare/8.3.1...8.3.2
[8.3.1]: https://github.com/sebastianbergmann/phpunit/compare/8.3.0...8.3.1
[8.3.0]: https://github.com/sebastianbergmann/phpunit/compare/8.2.5...8.3.0

