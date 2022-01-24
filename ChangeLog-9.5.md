# Changes in PHPUnit 9.5

All notable changes of the PHPUnit 9.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.5.13] - 2022-01-24

### Fixed

* [#4871](https://github.com/sebastianbergmann/phpunit/issues/4871): Class `SebastianBergmann\CodeCoverage\Filter` is not found during PHPT tests when PHPUnit is used from PHAR

## [9.5.12] - 2022-01-21

### Fixed

* [#4799](https://github.com/sebastianbergmann/phpunit/pull/4799): Memory leaks in `PHPUnit\Framework\TestSuite` class
* [#4857](https://github.com/sebastianbergmann/phpunit/pull/4857): Result of `debug_backtrace()` is not used correctly

## [9.5.11] - 2021-12-25

### Changed

* [#4812](https://github.com/sebastianbergmann/phpunit/issues/4812): Do not enforce time limits when a debugging session through DBGp is active
* [#4835](https://github.com/sebastianbergmann/phpunit/issues/4835): Support for `$GLOBALS['_composer_autoload_path']` introduced in Composer 2.2

### Fixed

* [#4840](https://github.com/sebastianbergmann/phpunit/pull/4840): TestDox prettifying for class names does not correctly handle diacritics
* [#4846](https://github.com/sebastianbergmann/phpunit/pull/4846): Composer proxy script is not ignored

## [9.5.10] - 2021-09-25

### Changed

* PHPUnit no longer converts PHP deprecations to exceptions by default (configure `convertDeprecationsToExceptions="true"` to enable this)
* The PHPUnit XML configuration file generator now configures `convertDeprecationsToExceptions="true"`

### Fixed

* [#4772](https://github.com/sebastianbergmann/phpunit/pull/4772): TestDox HTML report not displayed correctly when browser has custom colour settings

## [9.5.9] - 2021-08-31

### Fixed

* [#4750](https://github.com/sebastianbergmann/phpunit/issues/4750): Automatic return value generation leads to invalid (and superfluous) test double code generation when a stubbed method returns `*|false`
* [#4751](https://github.com/sebastianbergmann/phpunit/issues/4751): Configuration validation fails when using brackets in glob pattern

## [9.5.8] - 2021-07-31

### Fixed

* [#4740](https://github.com/sebastianbergmann/phpunit/issues/4740): `phpunit.phar` does not work with PHP 8.1

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

[9.5.13]: https://github.com/sebastianbergmann/phpunit/compare/9.5.12...9.5.13
[9.5.12]: https://github.com/sebastianbergmann/phpunit/compare/9.5.11...9.5.12
[9.5.11]: https://github.com/sebastianbergmann/phpunit/compare/9.5.10...9.5.11
[9.5.10]: https://github.com/sebastianbergmann/phpunit/compare/9.5.9...9.5.10
[9.5.9]: https://github.com/sebastianbergmann/phpunit/compare/9.5.8...9.5.9
[9.5.8]: https://github.com/sebastianbergmann/phpunit/compare/9.5.7...9.5.8
[9.5.7]: https://github.com/sebastianbergmann/phpunit/compare/9.5.6...9.5.7
[9.5.6]: https://github.com/sebastianbergmann/phpunit/compare/9.5.5...9.5.6
[9.5.5]: https://github.com/sebastianbergmann/phpunit/compare/9.5.4...9.5.5
[9.5.4]: https://github.com/sebastianbergmann/phpunit/compare/9.5.3...9.5.4
[9.5.3]: https://github.com/sebastianbergmann/phpunit/compare/9.5.2...9.5.3
[9.5.2]: https://github.com/sebastianbergmann/phpunit/compare/9.5.1...9.5.2
[9.5.1]: https://github.com/sebastianbergmann/phpunit/compare/9.5.0...9.5.1
[9.5.0]: https://github.com/sebastianbergmann/phpunit/compare/9.4.4...9.5.0
