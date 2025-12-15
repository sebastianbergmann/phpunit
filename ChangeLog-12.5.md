# Changes in PHPUnit 12.5

All notable changes of the PHPUnit 12.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.5.4] - 2025-12-15

### Changed

* The `#[AllowMockObjectsWithoutExpectations]` attribute can now be used on the method level

### Fixed

* [#6446](https://github.com/sebastianbergmann/phpunit/issues/6446): Test runner crashes with `Timer::start() has to be called before Timer::stop()`

## [12.5.3] - 2025-12-11

### Changed

* The message emitted when a test method creates a mock object but does not configure any expectations for it has been improved

## [12.5.2] - 2025-12-08

### Added

* Attribute `#[AllowMockObjectsWithoutExpectations]` for excluding tests from the check that emits the notice for test methods that create a mock object but do not configure an expectation for it

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

[12.5.4]: https://github.com/sebastianbergmann/phpunit/compare/12.5.3...12.5.4
[12.5.3]: https://github.com/sebastianbergmann/phpunit/compare/12.5.2...12.5.3
[12.5.2]: https://github.com/sebastianbergmann/phpunit/compare/12.5.1...12.5.2
[12.5.1]: https://github.com/sebastianbergmann/phpunit/compare/12.5.0...12.5.1
[12.5.0]: https://github.com/sebastianbergmann/phpunit/compare/12.4.5...12.5.0
