# Changes in PHPUnit 8.5

All notable changes of the PHPUnit 8.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [8.5.31] - 2022-10-28

### Fixed

* [#5076](https://github.com/sebastianbergmann/phpunit/issues/5076): Test Runner does not warn about conflicting options

## [8.5.30] - 2022-09-25

### Changed

* The configuration generator now asks for a cache directory

### Fixed

* [#4913](https://github.com/sebastianbergmann/phpunit/issues/4913): Failed `assert()` should show a backtrace
* [#4966](https://github.com/sebastianbergmann/phpunit/issues/4966): `TestCase::assertSame()` (and related exact comparisons) must compare `float` exactly

## [8.5.29] - 2022-08-22

### Changed

* [#5033](https://github.com/sebastianbergmann/phpunit/issues/5033): Do not depend on phpspec/prophecy

## [8.5.28] - 2022-07-29

### Fixed

* [#5015](https://github.com/sebastianbergmann/phpunit/pull/5015): Ukraine banner unreadable on black background
* [#5016](https://github.com/sebastianbergmann/phpunit/issues/5016): PHPUnit 8.5.27 does not work on PHP 7.2.0-7.2.18 and PHP 7.3.0-7.3.5

## [8.5.27] - 2022-06-19

### Fixed

* [#4950](https://github.com/sebastianbergmann/phpunit/issues/4950): False error on `atMost()` invocation rule without call
* [#4962](https://github.com/sebastianbergmann/phpunit/issues/4962): Ukraine banner unreadable on white background

## [8.5.26] - 2022-04-01

### Fixed

* [#4938](https://github.com/sebastianbergmann/phpunit/issues/4938): Test Double code generator does not handle `void` return type declaration on `__clone()` methods

## [8.5.25] - 2022-03-16

### Fixed

* [#4934](https://github.com/sebastianbergmann/phpunit/issues/4934): Code Coverage does not work with PHPUnit 8.5.24 PHAR on PHP 7

## [8.5.24] - 2022-03-05 - #StandWithUkraine

### Changed

* [#4874](https://github.com/sebastianbergmann/phpunit/pull/4874): `PHP_FLOAT_EPSILON` is now used instead of hardcoded `0.0000000001` in `PHPUnit\Framework\Constraint\IsIdentical`

### Fixed

* When the HTML code coverage report's configured low upper bound is larger than the high lower bound then the default values are used instead

## [8.5.23] - 2022-01-21

### Fixed

* [#4799](https://github.com/sebastianbergmann/phpunit/pull/4799): Memory leaks in `PHPUnit\Framework\TestSuite` class
* [#4857](https://github.com/sebastianbergmann/phpunit/pull/4857): Result of `debug_backtrace()` is not used correctly

## [8.5.22] - 2021-12-25

### Changed

* [#4812](https://github.com/sebastianbergmann/phpunit/issues/4812): Do not enforce time limits when a debugging session through DBGp is active
* [#4835](https://github.com/sebastianbergmann/phpunit/issues/4835): Support for `$GLOBALS['_composer_autoload_path']` introduced in Composer 2.2

### Fixed

* [#4840](https://github.com/sebastianbergmann/phpunit/pull/4840): TestDox prettifying for class names does not correctly handle diacritics
* [#4846](https://github.com/sebastianbergmann/phpunit/pull/4846): Composer proxy script is not ignored

## [8.5.21] - 2021-09-25

### Changed

* PHPUnit no longer converts PHP deprecations to exceptions by default (configure `convertDeprecationsToExceptions="true"` to enable this)
* The PHPUnit XML configuration file generator now configures `convertDeprecationsToExceptions="true"`

### Fixed

* [#4772](https://github.com/sebastianbergmann/phpunit/pull/4772): TestDox HTML report not displayed correctly when browser has custom colour settings

## [8.5.20] - 2021-08-31

### Fixed

* [#4751](https://github.com/sebastianbergmann/phpunit/issues/4751): Configuration validation fails when using brackets in glob pattern

## [8.5.19] - 2021-07-31

### Fixed

* [#4740](https://github.com/sebastianbergmann/phpunit/issues/4740): `phpunit.phar` does not work with PHP 8.1

## [8.5.18] - 2021-07-19

### Fixed

* [#4720](https://github.com/sebastianbergmann/phpunit/issues/4720): PHPUnit does not verify its own PHP extension requirements

## [8.5.17] - 2021-06-23

### Changed

* PHPUnit now errors out on startup when `PHP_VERSION` contains a value that is not compatible with `version_compare()`, for instance `X.Y.Z-(to be removed in future macOS)`

## [8.5.16] - 2021-06-05

### Changed

* The test result cache (the storage for which is implemented in `PHPUnit\Runner\DefaultTestResultCache`) no longer uses PHP's `serialize()` and `unserialize()` functions for persistence. It now uses a versioned JSON format instead that is independent of PHP implementation details (see [#3581](https://github.com/sebastianbergmann/phpunit/issues/3581) and [#4662](https://github.com/sebastianbergmann/phpunit/pull/4662) for examples why this is a problem). When PHPUnit tries to load the test result cache from a file that does not exist, or from a file that does not contain data in JSON format, or from a file that contains data in a JSON format version other than the one used by the currently running PHPUnit version, then this is considered to be a "cache miss". An empty `DefaultTestResultCache` object is created in this case. This should also prevent PHPUnit from crashing when trying to load a test result cache file created by a different version of PHPUnit (see [#4580](https://github.com/sebastianbergmann/phpunit/issues/4580) for example).

### Fixed

* [#4663](https://github.com/sebastianbergmann/phpunit/issues/4663): `TestCase::expectError()` works on PHP 7.3, but not on PHP >= 7.4
* [#4678](https://github.com/sebastianbergmann/phpunit/pull/4678): Stubbed methods with `iterable` return types should return empty array by default
* [#4692](https://github.com/sebastianbergmann/phpunit/issues/4692): Annotations in single-line doc-comments are not handled correctly
* [#4694](https://github.com/sebastianbergmann/phpunit/issues/4694): `TestCase::getMockFromWsdl()` does not work with PHP 8.1-dev

## [8.5.15] - 2021-03-17

### Fixed

* [#4591](https://github.com/sebastianbergmann/phpunit/issues/4591): TeamCity logger logs warnings as test failures

## [8.5.14] - 2021-01-17

### Fixed

* [#4535](https://github.com/sebastianbergmann/phpunit/issues/4535): `getMockFromWsdl()` does not handle methods that do not have parameters correctly
* [#4572](https://github.com/sebastianbergmann/phpunit/issues/4572): Schema validation does not work with `%xx` sequences in path to `phpunit.xsd`
* [#4575](https://github.com/sebastianbergmann/phpunit/issues/4575): PHPUnit 8.5 incompatibility with PHP 8.1

## [8.5.13] - 2020-12-01

### Fixed

* Running tests in isolated processes did not work with PHP 8 on Windows

## [8.5.12] - 2020-11-30

### Changed

* Changed PHP version constraint in `composer.json` from `^7.2` to `>=7.2` to allow the installation of PHPUnit 8.5 on PHP 8. Please note that the code coverage functionality is not available for PHPUnit 8.5 on PHP 8.

### Fixed

* [#4529](https://github.com/sebastianbergmann/phpunit/issues/4529): Debug mode of Xdebug 2 is not disabled for PHPT tests

## [8.5.11] - 2020-11-27

### Changed

* Bumped required version of `phpunit/php-code-coverage`

## [8.5.10] - 2020-11-27

### Added

* Support for Xdebug 3

### Fixed

* [#4516](https://github.com/sebastianbergmann/phpunit/issues/4516): `phpunit/phpunit-selenium` does not work with PHPUnit 8.5.9

## [8.5.9] - 2020-11-10

### Fixed

* [#3965](https://github.com/sebastianbergmann/phpunit/issues/3965): Process Isolation throws exceptions when PHPDBG is used
* [#4470](https://github.com/sebastianbergmann/phpunit/pull/4470): Infinite recursion when `--static-backup --strict-global-state` is used

## [8.5.8] - 2020-06-22

### Fixed

* [#4312](https://github.com/sebastianbergmann/phpunit/issues/4312): Fix for [#4299](https://github.com/sebastianbergmann/phpunit/issues/4299) breaks backward compatibility

## [8.5.7] - 2020-06-21

### Fixed

* [#4299](https://github.com/sebastianbergmann/phpunit/issues/4299): "No tests executed" does not always result in exit code `1`
* [#4306](https://github.com/sebastianbergmann/phpunit/issues/4306): Exceptions during code coverage driver initialization are not handled correctly

## [8.5.6] - 2020-06-15

### Fixed

* [#4211](https://github.com/sebastianbergmann/phpunit/issues/4211): `phpdbg_*()` functions are scoped to `PHPUnit\phpdbg_*()`

## [8.5.5] - 2020-05-22

### Fixed

* [#4033](https://github.com/sebastianbergmann/phpunit/issues/4033): Unexpected behaviour when `$GLOBALS` is deleted

## [8.5.4] - 2020-04-23

### Changed

* Changed how `PHPUnit\TextUI\Command` passes warnings to `PHPUnit\TextUI\TestRunner`

## [8.5.3] - 2020-03-31

### Fixed

* [#4017](https://github.com/sebastianbergmann/phpunit/issues/4017): Do not suggest refactoring to something that is also deprecated
* [#4133](https://github.com/sebastianbergmann/phpunit/issues/4133): `expectExceptionMessageRegExp()` has been removed in PHPUnit 9 without a deprecation warning being given in PHPUnit 8
* [#4139](https://github.com/sebastianbergmann/phpunit/issues/4139): Cannot double interfaces that declare a constructor with PHP 8
* [#4144](https://github.com/sebastianbergmann/phpunit/issues/4144): Empty objects are converted to empty arrays in JSON comparison failure diff

## [8.5.2] - 2020-01-08

### Removed

* `eval-stdin.php` has been removed, it was not used anymore since PHPUnit 7.2.7

## [8.5.1] - 2019-12-25

### Changed

* `eval-stdin.php` can now only be executed with `cli` and `phpdbg`

### Fixed

* [#3983](https://github.com/sebastianbergmann/phpunit/issues/3983): Deprecation warning given too eagerly

## [8.5.0] - 2019-12-06

### Added

* [#3911](https://github.com/sebastianbergmann/phpunit/issues/3911): Support combined use of `addMethods()` and `onlyMethods()`
* [#3949](https://github.com/sebastianbergmann/phpunit/issues/3949): Introduce specialized assertions `assertFileEqualsCanonicalizing()`, `assertFileEqualsIgnoringCase()`, `assertStringEqualsFileCanonicalizing()`, `assertStringEqualsFileIgnoringCase()`, `assertFileNotEqualsCanonicalizing()`, `assertFileNotEqualsIgnoringCase()`, `assertStringNotEqualsFileCanonicalizing()`, and `assertStringNotEqualsFileIgnoringCase()` as alternative to using `assertFileEquals()` etc. with optional parameters

### Changed

* [#3860](https://github.com/sebastianbergmann/phpunit/pull/3860): Deprecate invoking PHPUnit commandline test runner with just a class name
* [#3950](https://github.com/sebastianbergmann/phpunit/issues/3950): Deprecate optional parameters of `assertFileEquals()` etc.
* [#3955](https://github.com/sebastianbergmann/phpunit/issues/3955): Deprecate support for doubling multiple interfaces

### Fixed

* [#3953](https://github.com/sebastianbergmann/phpunit/issues/3953): Code Coverage for test executed in isolation does not work when the PHAR is used
* [#3967](https://github.com/sebastianbergmann/phpunit/issues/3967): Cannot double interface that extends interface that extends `\Throwable`
* [#3968](https://github.com/sebastianbergmann/phpunit/pull/3968): Test class run in a separate PHP process are passing when `exit` called inside

[8.5.31]: https://github.com/sebastianbergmann/phpunit/compare/8.5.30...8.5.31
[8.5.30]: https://github.com/sebastianbergmann/phpunit/compare/8.5.29...8.5.30
[8.5.29]: https://github.com/sebastianbergmann/phpunit/compare/8.5.28...8.5.29
[8.5.28]: https://github.com/sebastianbergmann/phpunit/compare/8.5.27...8.5.28
[8.5.27]: https://github.com/sebastianbergmann/phpunit/compare/8.5.26...8.5.27
[8.5.26]: https://github.com/sebastianbergmann/phpunit/compare/8.5.25...8.5.26
[8.5.25]: https://github.com/sebastianbergmann/phpunit/compare/8.5.24...8.5.25
[8.5.24]: https://github.com/sebastianbergmann/phpunit/compare/8.5.23...8.5.24
[8.5.23]: https://github.com/sebastianbergmann/phpunit/compare/8.5.22...8.5.23
[8.5.22]: https://github.com/sebastianbergmann/phpunit/compare/8.5.21...8.5.22
[8.5.21]: https://github.com/sebastianbergmann/phpunit/compare/8.5.20...8.5.21
[8.5.20]: https://github.com/sebastianbergmann/phpunit/compare/8.5.19...8.5.20
[8.5.19]: https://github.com/sebastianbergmann/phpunit/compare/8.5.18...8.5.19
[8.5.18]: https://github.com/sebastianbergmann/phpunit/compare/8.5.17...8.5.18
[8.5.17]: https://github.com/sebastianbergmann/phpunit/compare/8.5.16...8.5.17
[8.5.16]: https://github.com/sebastianbergmann/phpunit/compare/8.5.15...8.5.16
[8.5.15]: https://github.com/sebastianbergmann/phpunit/compare/8.5.14...8.5.15
[8.5.14]: https://github.com/sebastianbergmann/phpunit/compare/8.5.13...8.5.14
[8.5.13]: https://github.com/sebastianbergmann/phpunit/compare/8.5.12...8.5.13
[8.5.12]: https://github.com/sebastianbergmann/phpunit/compare/8.5.11...8.5.12
[8.5.11]: https://github.com/sebastianbergmann/phpunit/compare/8.5.10...8.5.11
[8.5.10]: https://github.com/sebastianbergmann/phpunit/compare/8.5.9...8.5.10
[8.5.9]: https://github.com/sebastianbergmann/phpunit/compare/8.5.8...8.5.9
[8.5.8]: https://github.com/sebastianbergmann/phpunit/compare/8.5.7...8.5.8
[8.5.7]: https://github.com/sebastianbergmann/phpunit/compare/8.5.6...8.5.7
[8.5.6]: https://github.com/sebastianbergmann/phpunit/compare/8.5.5...8.5.6
[8.5.5]: https://github.com/sebastianbergmann/phpunit/compare/8.5.4...8.5.5
[8.5.4]: https://github.com/sebastianbergmann/phpunit/compare/8.5.3...8.5.4
[8.5.3]: https://github.com/sebastianbergmann/phpunit/compare/8.5.2...8.5.3
[8.5.2]: https://github.com/sebastianbergmann/phpunit/compare/8.5.1...8.5.2
[8.5.1]: https://github.com/sebastianbergmann/phpunit/compare/8.5.0...8.5.1
[8.5.0]: https://github.com/sebastianbergmann/phpunit/compare/8.4.3...8.5.0
