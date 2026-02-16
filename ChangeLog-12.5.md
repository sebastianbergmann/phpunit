# Changes in PHPUnit 12.5

All notable changes of the PHPUnit 12.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.5.12] - 2026-02-16

### Fixed

* [#6511](https://github.com/sebastianbergmann/phpunit/issues/6511): TestDox variables out of order with named arguments
* [#6514](https://github.com/sebastianbergmann/phpunit/issues/6514): `<ini />` can silently fail

## [12.5.11] - 2026-02-10

### Deprecated

* [#6510](https://github.com/sebastianbergmann/phpunit/issues/6510): Deprecate using `with*()` on test stubs

### Fixed

* [#6503](https://github.com/sebastianbergmann/phpunit/issues/6503): Temporary file used by `SourceMapper` may be deleted prematurely when multiple PHPUnit processes run in parallel
* [#6509](https://github.com/sebastianbergmann/phpunit/issues/6509): "No expectations were configured for the mock object ..." notice is emitted when `with()` is used without `expects()`

## [12.5.10] - 2026-02-08

### Fixed

* [#6495](https://github.com/sebastianbergmann/phpunit/pull/6495): Source map for issue trigger identification is regenerated in process isolation child processes

## [12.5.9] - 2026-02-05

### Added

* [#6488](https://github.com/sebastianbergmann/phpunit/issues/6488): Allow disabling issue trigger identification for improved performance

### Fixed

* [#6486](https://github.com/sebastianbergmann/phpunit/issues/6486): Incorrect file name reported for errors for test methods declared in traits
* [#6490](https://github.com/sebastianbergmann/phpunit/pull/6490): Incorrect test count when tests are skipped in before-class method

## [12.5.8] - 2026-01-27

### Changed

* To prevent Poisoned Pipeline Execution (PPE) attacks using prepared `.coverage` files in pull requests, a PHPT test will no longer be run if the temporary file for writing code coverage information already exists before the test runs

## [12.5.7] - 2026-01-24

### Fixed

* [#6362](https://github.com/sebastianbergmann/phpunit/issues/6362): Manually instantiated test doubles are broken since PHPUnit 11.2
* [#6470](https://github.com/sebastianbergmann/phpunit/issues/6470): Infinite recursion in `Count::getCountOf()` for unusal implementations of `Iterator` or `IteratorAggregate`

## [12.5.6] - 2026-01-16

### Changed

* Reverted a change that caused a [build failure](https://github.com/php/php-src/actions/runs/21052584327/job/60542023395#step:14:3729) for the [PHP project's nightly community job](https://phpunit.expert/articles/how-php-and-its-ecosystem-test-each-other.html?ref=github)

## [12.5.5] - 2026-01-15

### Changed

* `PHPUnit\Framework\MockObject` exceptions are now subtypes of `PHPUnit\Exception`

### Deprecated

* [#6461](https://github.com/sebastianbergmann/phpunit/issues/6461): `any()` matcher (soft deprecation)

### Fixed

* [#6470](https://github.com/sebastianbergmann/phpunit/issues/6470): Mocking a class with a property hook setter accepting more types than the property results in a fatal error

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

[12.5.12]: https://github.com/sebastianbergmann/phpunit/compare/12.5.11...12.5.12
[12.5.11]: https://github.com/sebastianbergmann/phpunit/compare/12.5.10...12.5.11
[12.5.10]: https://github.com/sebastianbergmann/phpunit/compare/12.5.9...12.5.10
[12.5.9]: https://github.com/sebastianbergmann/phpunit/compare/12.5.8...12.5.9
[12.5.8]: https://github.com/sebastianbergmann/phpunit/compare/12.5.7...12.5.8
[12.5.7]: https://github.com/sebastianbergmann/phpunit/compare/12.5.6...12.5.7
[12.5.6]: https://github.com/sebastianbergmann/phpunit/compare/12.5.5...12.5.6
[12.5.5]: https://github.com/sebastianbergmann/phpunit/compare/12.5.4...12.5.5
[12.5.4]: https://github.com/sebastianbergmann/phpunit/compare/12.5.3...12.5.4
[12.5.3]: https://github.com/sebastianbergmann/phpunit/compare/12.5.2...12.5.3
[12.5.2]: https://github.com/sebastianbergmann/phpunit/compare/12.5.1...12.5.2
[12.5.1]: https://github.com/sebastianbergmann/phpunit/compare/12.5.0...12.5.1
[12.5.0]: https://github.com/sebastianbergmann/phpunit/compare/12.4.5...12.5.0
