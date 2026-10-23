# Changes in PHPUnit 14.0

All notable changes of the PHPUnit 14.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [14.0.0] - 2027-02-05

### Changed

* [#6498](https://github.com/sebastianbergmann/phpunit/issues/6498): Do not depend on `expects()` for test stub implementation
* [#6580](https://github.com/sebastianbergmann/phpunit/issues/6580): Move/Rename events for issues triggered outside of tests
* [#6956](https://github.com/sebastianbergmann/phpunit/issues/6956): Redesign the test reordering subsystem
* [#7004](https://github.com/sebastianbergmann/phpunit/issues/7004): Guarantee that tests, test suites, and hook methods are bracketed by events
* A test that runs another test, for instance by calling `run()` on a `TestCase` object, now errors

### Deprecated

* [#6538](https://github.com/sebastianbergmann/phpunit/issues/6538): `id()` and `after()` for mock object expectations
* [#6561](https://github.com/sebastianbergmann/phpunit/issues/6561): `expectExceptionMessage()`

### Removed

* [#6462](https://github.com/sebastianbergmann/phpunit/issues/6462): `any()` matcher
* [#6491](https://github.com/sebastianbergmann/phpunit/issues/6491): Support for PHP 8.4
* [#6508](https://github.com/sebastianbergmann/phpunit/issues/6508): Support for using `with()` without `expects()`
* [#6516](https://github.com/sebastianbergmann/phpunit/issues/6516): `--log-events-verbose-text <file>` CLI option
* [#6951](https://github.com/sebastianbergmann/phpunit/pull/6951): `LogicalNot::negate()` and `Constraint::failureDescriptionInContext()`
* [#7050](https://github.com/sebastianbergmann/phpunit/issues/7050): `--random-order-seed <N>` CLI option
* `--cache-result` CLI option, use `--record-test-run-history` instead
* `--do-not-cache-result` CLI option, use `--do-not-record-test-run-history` instead
* `cacheResult` XML configuration attribute, use `recordTestRunHistory` instead
* `PHPUnit\TextUI\Configuration\Configuration::cacheResult()`, use `PHPUnit\TextUI\Configuration\Configuration::recordTestRunHistory()` instead
* `PHPUnit\TextUI\Configuration\Configuration::testResultCacheFile()`, use `PHPUnit\TextUI\Configuration\Configuration::testRunHistoryFile()` instead

### Fixed

* A test that is skipped in a before-test method and errors in an after-test method is counted twice
* Output that a test printed before it was skipped in a before-test method is missing from the JUnit XML logfile

[14.0.0]: https://github.com/sebastianbergmann/phpunit/compare/13.5...main
