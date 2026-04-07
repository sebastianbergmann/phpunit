# Changes in PHPUnit 13.2

All notable changes of the PHPUnit 13.2 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.2.0] - 2026-06-05

### Added

* [#3387](https://github.com/sebastianbergmann/phpunit/issues/3387): Specify a list of tests to run
* [#4201](https://github.com/sebastianbergmann/phpunit/issues/4201): Handle interrupts and display current test results
* [#6559](https://github.com/sebastianbergmann/phpunit/issues/6559): Improved API for exception message expectations
* [#6566](https://github.com/sebastianbergmann/phpunit/pull/6566): Allow `--stop-on-defect`, `--stop-on-error`, etc. to accept an optional threshold
* [#6567](https://github.com/sebastianbergmann/phpunit/issues/6567): Make diff context lines configurable
* [#6574](https://github.com/sebastianbergmann/phpunit/issues/6574): Improve `willReturnMap()` with constraint support and strict matching

### Deprecated

* [#6560](https://github.com/sebastianbergmann/phpunit/issues/6560): Soft-deprecate `expectExceptionMessage()`, use `expectExceptionMessageIsOrContains()` instead

[13.2.0]: https://github.com/sebastianbergmann/phpunit/compare/13.1...main
