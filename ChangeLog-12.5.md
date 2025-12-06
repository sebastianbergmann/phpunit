# Changes in PHPUnit 12.5

All notable changes of the PHPUnit 12.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.5.1] - 2025-12-06

### Added

* `TestCase::getStubBuilder()` (analogous to `TestCase::getMockBuilder()`) for creating (partial) test stubs using a fluent API

## [12.5.0] - 2025-12-05

### Added

* [#6376](https://github.com/sebastianbergmann/phpunit/issues/6376): `--all` CLI option to ignore test selection configured in XML configuration file
* [#6422](https://github.com/sebastianbergmann/phpunit/issues/6422): Make `<source>` element in XML code coverage report optional

### Changed

* [#6380](https://github.com/sebastianbergmann/phpunit/pull/6380): Allow `Throwable` in `expectExceptionObject()`
* A PHPUnit notice is now emitted for test methods that create a mock object but do not configure an expectation for it

[12.5.1]: https://github.com/sebastianbergmann/phpunit/compare/12.5.0...12.5.1
[12.5.0]: https://github.com/sebastianbergmann/phpunit/compare/12.4.5...12.5.0
