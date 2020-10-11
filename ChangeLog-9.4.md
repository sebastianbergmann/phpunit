# Changes in PHPUnit 9.4

All notable changes of the PHPUnit 9.4 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

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

[9.4.1]: https://github.com/sebastianbergmann/phpunit/compare/9.4.0...9.4.1
[9.4.0]: https://github.com/sebastianbergmann/phpunit/compare/9.3.11...9.4.0
