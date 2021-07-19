# Changes in PHPUnit 9.5

All notable changes of the PHPUnit 9.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.5.7] - 2021-07-19

### Fixed

* [#4720](https://github.com/sebastianbergmann/phpunit/issues/4720): PHPUnit does not verify its own PHP extension requirements
* [#4735](https://github.com/sebastianbergmann/phpunit/issues/4735): Automated return value generation does not work for stubbed methods that return `*|false`

## [9.5.6] - 2021-06-23

### Changed

* PHPUnit now errors out on startup when `PHP_VERSION` contains a value that is not compatible with `version_compare()`, for instance `X.Y.Z-(to be removed in future macOS)`

## [9.5.5] - 2021-06-05

### Changed

* The test result cache (the storage for which is implemented in `PHPUnit\Runner\DefaultTestResultCache`) no longer uses PHP's `serialize()` and `unserialize()` functions for persistence. It now uses a versioned JSON format instead that is independent of PHP implementation details (see [#3581](https://github.com/sebastianbergmann/phpunit/issues/3581) and [#4662](https://github.com/sebastianbergmann/phpunit/pull/4662) for examples why this is a problem). When PHPUnit tries to load the test result cache from a file that does not exist, or from a file that does not contain data in JSON format, or from a file that contains data in a JSON format version other than the one used by the currently running PHPUnit version, then this is considered to be a "cache miss". An empty `DefaultTestResultCache` object is created in this case. This should also prevent PHPUnit from crashing when trying to load a test result cache file created by a different version of PHPUnit (see [#4580](https://github.com/sebastianbergmann/phpunit/issues/4580) for example).

### Fixed

* [#4632](https://github.com/sebastianbergmann/phpunit/issues/4632): TestDox result printer does not handle repeated test execution correctly
* [#4678](https://github.com/sebastianbergmann/phpunit/pull/4678): Stubbed methods with `iterable` return types should return empty array by default
* [#4692](https://github.com/sebastianbergmann/phpunit/issues/4692): Annotations in single-line doc-comments are not handled correctly
* [#4694](https://github.com/sebastianbergmann/phpunit/issues/4694): `TestCase::getMockFromWsdl()` does not work with PHP 8.1-dev

## [9.5.4] - 2021-03-23

### Fixed

* [#4630](https://github.com/sebastianbergmann/phpunit/issues/4630): Empty test case class causes error in TestDox XML logger

## [9.5.3] - 2021-03-17

### Fixed

* [#4591](https://github.com/sebastianbergmann/phpunit/issues/4591): TeamCity logger logs warnings as test failures
* [#4620](https://github.com/sebastianbergmann/phpunit/issues/4620): No useful output when an error occurs in the bootstrap script

## [9.5.2] - 2021-02-02

### Fixed

* [#4573](https://github.com/sebastianbergmann/phpunit/issues/4573): No stack trace printed when PHPUnit is used from PHAR
* [#4590](https://github.com/sebastianbergmann/phpunit/issues/4590): `--coverage-text` CLI option is documented wrong

## [9.5.1] - 2021-01-17

### Fixed

* [#4572](https://github.com/sebastianbergmann/phpunit/issues/4572): Schema validation does not work with `%xx` sequences in path to `phpunit.xsd`

## [9.5.0] - 2020-12-04

### Changed

* [#4490](https://github.com/sebastianbergmann/phpunit/issues/4490): Emit Error instead of Warning when test case class cannot be instantiated
* [#4491](https://github.com/sebastianbergmann/phpunit/issues/4491): Emit Error instead of Warning when data provider does not work correctly
* [#4492](https://github.com/sebastianbergmann/phpunit/issues/4492): Emit Error instead of Warning when test double configuration is invalid
* [#4493](https://github.com/sebastianbergmann/phpunit/issues/4493): Emit error when (configured) test directory does not exist

### Fixed

* [#4535](https://github.com/sebastianbergmann/phpunit/issues/4535): `getMockFromWsdl()` does not handle methods that do not have parameters correctly

[9.5.7]: https://github.com/sebastianbergmann/phpunit/compare/9.5.5...9.5.7
[9.5.6]: https://github.com/sebastianbergmann/phpunit/compare/9.5.5...9.5.6
[9.5.5]: https://github.com/sebastianbergmann/phpunit/compare/9.5.4...9.5.5
[9.5.4]: https://github.com/sebastianbergmann/phpunit/compare/9.5.3...9.5.4
[9.5.3]: https://github.com/sebastianbergmann/phpunit/compare/9.5.2...9.5.3
[9.5.2]: https://github.com/sebastianbergmann/phpunit/compare/9.5.1...9.5.2
[9.5.1]: https://github.com/sebastianbergmann/phpunit/compare/9.5.0...9.5.1
[9.5.0]: https://github.com/sebastianbergmann/phpunit/compare/9.4.4...9.5.0
