# Changes in PHPUnit 12.3

All notable changes of the PHPUnit 12.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.3.0] - 2025-08-01

### Added

* `Configuration::includeTestSuites()` and `Configuration::excludeTestSuites()`

### Deprecated

* [#6229](https://github.com/sebastianbergmann/phpunit/issues/6229): `Configuration::includeTestSuite()`, use `Configuration::includeTestSuites()` instead
* [#6229](https://github.com/sebastianbergmann/phpunit/issues/6229): `Configuration::excludeTestSuite()`, use `Configuration::excludeTestSuites()` instead

[12.3.0]: https://github.com/sebastianbergmann/phpunit/compare/12.2...main
