# Changes in PHPUnit 12.3

All notable changes of the PHPUnit 12.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.3.0] - 2025-08-01

### Added

* [#3795](https://github.com/sebastianbergmann/phpunit/issues/3795): Bootstrap scripts specific to test suites
* `TestRunner\ChildProcessErrored` event
* `Configuration::includeTestSuites()` and `Configuration::excludeTestSuites()`

### Changed

* [#6237](https://github.com/sebastianbergmann/phpunit/issues/6237): Do not run tests when code coverage analysis is requested but code coverage data cannot be collected

### Deprecated

* [#6229](https://github.com/sebastianbergmann/phpunit/issues/6229): `Configuration::includeTestSuite()`, use `Configuration::includeTestSuites()` instead
* [#6229](https://github.com/sebastianbergmann/phpunit/issues/6229): `Configuration::excludeTestSuite()`, use `Configuration::excludeTestSuites()` instead
* [#6246](https://github.com/sebastianbergmann/phpunit/issues/6246): Using `#[CoversNothing]` on a test method

[12.3.0]: https://github.com/sebastianbergmann/phpunit/compare/12.2...main
