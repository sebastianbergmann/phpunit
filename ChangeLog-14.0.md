# Changes in PHPUnit 14.0

All notable changes of the PHPUnit 14.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [14.0.0] - 2027-02-05

### Changed

* [#6956](https://github.com/sebastianbergmann/phpunit/issues/6956): Redesign the test reordering subsystem

### Removed

* [#6491](https://github.com/sebastianbergmann/phpunit/issues/6491): Support for PHP 8.4
* [#6516](https://github.com/sebastianbergmann/phpunit/issues/6516): `--log-events-verbose-text <file>` CLI option
* [#7050](https://github.com/sebastianbergmann/phpunit/issues/7050): `--random-order-seed <N>` CLI option
* `--cache-result` CLI option, use `--record-test-run-history` instead
* `--do-not-cache-result` CLI option, use `--do-not-record-test-run-history` instead
* `cacheResult` XML configuration attribute, use `recordTestRunHistory` instead
* `PHPUnit\TextUI\Configuration\Configuration::cacheResult()`, use `PHPUnit\TextUI\Configuration\Configuration::recordTestRunHistory()` instead
* `PHPUnit\TextUI\Configuration\Configuration::testResultCacheFile()`, use `PHPUnit\TextUI\Configuration\Configuration::testRunHistoryFile()` instead

[14.0.0]: https://github.com/sebastianbergmann/phpunit/compare/13.5...main
