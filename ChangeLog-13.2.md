# Changes in PHPUnit 13.2

All notable changes of the PHPUnit 13.2 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.2.0] - 2026-06-05

### Added

* [#3387](https://github.com/sebastianbergmann/phpunit/issues/3387): Specify a list of tests to run
* [#4201](https://github.com/sebastianbergmann/phpunit/issues/4201): Handle interrupts and display current test results
* [#5757](https://github.com/sebastianbergmann/phpunit/issues/5757): Add assertions for ignoring whitespace differences in strings
* [#5810](https://github.com/sebastianbergmann/phpunit/issues/5810): Do not dump arrays and objects in failure messages of `IsTrue`, `IsFalse`, `IsNull`, `IsFinite`, `IsInfinite`, and `IsNan` constraints
* [#5838](https://github.com/sebastianbergmann/phpunit/issues/5838): Inherit `#[RunTestsInSeparateProcesses]` from parent test classes
* [#6559](https://github.com/sebastianbergmann/phpunit/issues/6559): Improved API for exception message expectations
* [#6566](https://github.com/sebastianbergmann/phpunit/pull/6566): Allow `--stop-on-defect`, `--stop-on-error`, etc. to accept an optional threshold
* [#6567](https://github.com/sebastianbergmann/phpunit/issues/6567): Make diff context lines configurable
* [#6574](https://github.com/sebastianbergmann/phpunit/issues/6574): Improve `willReturnMap()` with constraint support and strict matching
* [#6575](https://github.com/sebastianbergmann/phpunit/issues/6575): `--list-test-ids` CLI option and enhance `--filter` CLI option to support test ID syntax
* [#6577](https://github.com/sebastianbergmann/phpunit/issues/6577): `--run-test-id <test-id>` CLI option that accepts a single test ID for exact matching
* [#6579](https://github.com/sebastianbergmann/phpunit/pull/6579): Properly handle issues triggered outside of tests

### Deprecated

* [#6560](https://github.com/sebastianbergmann/phpunit/issues/6560): Soft-deprecate `expectExceptionMessage()`, use `expectExceptionMessageIsOrContains()` instead

[13.2.0]: https://github.com/sebastianbergmann/phpunit/compare/13.1...main
