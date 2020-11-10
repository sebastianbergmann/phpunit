# Changes in PHPUnit 9.4

All notable changes of the PHPUnit 9.4 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.4.3] - 2020-11-10

### Fixed

* [#4446](https://github.com/sebastianbergmann/phpunit/pull/4446): The `--no-logging` and `--no-coverage` do not only affect XML configuration settings
* [#4499](https://github.com/sebastianbergmann/phpunit/pull/4499): Restore support for case-insensitive namespaced class names when invoking the test runner with the test case file name
* [#4514](https://github.com/sebastianbergmann/phpunit/issues/4514): `--fail-on-skipped` does not work

## [9.4.2] - 2020-10-19

### Added

* Added missing `PHPUnit\Framework\assertObjectEquals()` and `PHPUnit\Framework\objectEquals()` wrapper functions

### Changed

* `PHPUnit\Framework\Assert::assertObjectEquals()` is now `static`

## [9.4.1] - 2020-10-11

### Fixed

* [#4480](https://github.com/sebastianbergmann/phpunit/issues/4480): Methods with "static" return type (introduced in PHP 8) are not handled correctly by test double code generator

## [9.4.0] - 2020-10-02

### Added

* [#4462](https://github.com/sebastianbergmann/phpunit/pull/4462): Support for Cobertura XML report format
* [#4464](https://github.com/sebastianbergmann/phpunit/issues/4464): Filter based on covered (`@covers`) / used (`@uses`) units of code
* [#4467](https://github.com/sebastianbergmann/phpunit/issues/4467): Convenient custom comparison of objects

### Changed

* The PHPUnit XML configuration generator (that is invoked using the `--generate-configuration` CLI option) now asks for a cache directory (default: `.phpunit.cache`)

[9.4.3]: https://github.com/sebastianbergmann/phpunit/compare/9.4.2...9.4.3
[9.4.2]: https://github.com/sebastianbergmann/phpunit/compare/9.4.1...9.4.2
[9.4.1]: https://github.com/sebastianbergmann/phpunit/compare/9.4.0...9.4.1
[9.4.0]: https://github.com/sebastianbergmann/phpunit/compare/9.3.11...9.4.0
