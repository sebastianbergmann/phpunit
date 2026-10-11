# Changes in PHPUnit 13.5

All notable changes of the PHPUnit 13.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.5.0] - 2026-12-04

### Added

* [#7038](https://github.com/sebastianbergmann/phpunit/issues/7038): `--show-effective-configuration` to show what a configuration will execute, without executing it
* [#7039](https://github.com/sebastianbergmann/phpunit/issues/7039): `TestCase::suspendOutputBuffering()` and `TestCase::resumeOutputBuffering()` methods for capturing the output of a test method that
* [#7049](https://github.com/sebastianbergmann/phpunit/issues/7049): `--random-seed <N>` CLI option

### Changed

* [#7049](https://github.com/sebastianbergmann/phpunit/issues/7049): Renamed `phpunit:randomOrderSeed` to `phpunit:randomSeed` in Open Test Reporting XML logfiles

### Deprecated

* [#7049](https://github.com/sebastianbergmann/phpunit/issues/7049): `--random-order-seed <N>` CLI option


[13.5.0]: https://github.com/sebastianbergmann/phpunit/compare/13.4...main
