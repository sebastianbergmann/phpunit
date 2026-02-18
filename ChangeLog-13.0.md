# Changes in PHPUnit 13.0

All notable changes of the PHPUnit 13.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.0.5] - 2026-02-18

### Fixed

* [#6521](https://github.com/sebastianbergmann/phpunit/issues/6521): Performance regression in PHPUnit 11.5.54, PHPUnit 12.5.13, and PHPUnit 13.0.4

## [13.0.4] - 2026-02-18

### Fixed

* [#6489](https://github.com/sebastianbergmann/phpunit/pull/6489): Classification of self/direct/indirect deprecation triggers is not aligned with Symfony's bridge for PHPUnit

## [13.0.3] - 2026-02-16

### Fixed

* [#6511](https://github.com/sebastianbergmann/phpunit/issues/6511): TestDox variables out of order with named arguments
* [#6514](https://github.com/sebastianbergmann/phpunit/issues/6514): `<ini />` can silently fail

## [13.0.2] - 2026-02-10

### Deprecated

* [#6505](https://github.com/sebastianbergmann/phpunit/issues/6505): Calling `atLeast()` with an argument that is not positive
* [#6507](https://github.com/sebastianbergmann/phpunit/issues/6507): Support for using `with*()` without `expects()`

### Fixed

* [#6503](https://github.com/sebastianbergmann/phpunit/issues/6503): Temporary file used by `SourceMapper` may be deleted prematurely when multiple PHPUnit processes run in parallel
* [#6509](https://github.com/sebastianbergmann/phpunit/issues/6509): "No expectations were configured for the mock object ..." notice is emitted when `with()` is used without `expects()`

## [13.0.1] - 2026-02-08

### Fixed

* [#6495](https://github.com/sebastianbergmann/phpunit/pull/6495): Source map for issue trigger identification is regenerated in process isolation child processes
* [#6497](https://github.com/sebastianbergmann/phpunit/issues/6497): `method()` returns `InvocationMocker` instead of `InvocationStubber` for test stubs

## [13.0.0] - 2026-02-06

### Added

* [#6450](https://github.com/sebastianbergmann/phpunit/issues/6450): `TestCase::invokeTestMethod()` method for customizing test method invocation
* [#6455](https://github.com/sebastianbergmann/phpunit/issues/6455): `withParameterSetsInOrder()` and `withParameterSetsInAnyOrder()` for expecting calls to the same method of a mock object but with different arguments
* [#6466](https://github.com/sebastianbergmann/phpunit/issues/6466): Sealed test doubles
* [#6468](https://github.com/sebastianbergmann/phpunit/issues/6468): Configuration option to require sealed mock objects
* [#6477](https://github.com/sebastianbergmann/phpunit/pull/6477): `assertArraysAreIdentical()`, `assertArraysAreIdenticalIgnoringOrder()`, `assertArraysHaveIdenticalValues()`, `assertArraysHaveIdenticalValuesIgnoringOrder()`, `assertArraysAreEqual()`, `assertArraysAreEqualIgnoringOrder()`, `assertArraysHaveEqualValues()`, and `assertArraysHaveEqualValuesIgnoringOrder()` assertions
* `--test-files-file <file>` CLI option to configure a file that contains the paths to the test files to be loaded (one file per line); use this when using CLI arguments is not an option due to argument length limitations

### Deprecated

* [#6461](https://github.com/sebastianbergmann/phpunit/issues/6461): `any()` matcher (hard deprecation)

### Removed

* [#6054](https://github.com/sebastianbergmann/phpunit/issues/6054): `Assert::isType()`
* [#6057](https://github.com/sebastianbergmann/phpunit/issues/6057): `assertContainsOnly()` and `assertNotContainsOnly()`
* [#6061](https://github.com/sebastianbergmann/phpunit/issues/6061): `containsOnly()`
* [#6076](https://github.com/sebastianbergmann/phpunit/issues/6076): Support for PHP 8.3
* [#6141](https://github.com/sebastianbergmann/phpunit/issues/6141): `testClassName()` method on event value objects for hook methods called for test methods
* [#6230](https://github.com/sebastianbergmann/phpunit/issues/6230): `Configuration::includeTestSuite()` and `Configuration::excludeTestSuite()`
* [#6241](https://github.com/sebastianbergmann/phpunit/issues/6241): `--dont-report-useless-tests` CLI option
* [#6247](https://github.com/sebastianbergmann/phpunit/issues/6247): Support for using `#[CoversNothing]` on a test method
* [#6285](https://github.com/sebastianbergmann/phpunit/issues/6285): `#[RunClassInSeparateProcess]` attribute
* [#6356](https://github.com/sebastianbergmann/phpunit/issues/6356): Support for version constraint string argument without explicit version comparison operator

[13.0.5]: https://github.com/sebastianbergmann/phpunit/compare/13.0.4...13.0.5
[13.0.4]: https://github.com/sebastianbergmann/phpunit/compare/13.0.3...13.0.4
[13.0.3]: https://github.com/sebastianbergmann/phpunit/compare/13.0.2...13.0.3
[13.0.2]: https://github.com/sebastianbergmann/phpunit/compare/13.0.1...13.0.2
[13.0.1]: https://github.com/sebastianbergmann/phpunit/compare/13.0.0...13.0.1
[13.0.0]: https://github.com/sebastianbergmann/phpunit/compare/12.5...13.0.0
