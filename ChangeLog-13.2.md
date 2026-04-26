# Changes in PHPUnit 13.2

All notable changes of the PHPUnit 13.2 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.2.0] - 2026-06-05

### Added

* [#3387](https://github.com/sebastianbergmann/phpunit/issues/3387): Specify a list of tests to run
* [#4201](https://github.com/sebastianbergmann/phpunit/issues/4201): Handle interrupts and display current test results
* [#4501](https://github.com/sebastianbergmann/phpunit/issues/4501): Option to mark test as risky when it does not contribute to code coverage
* [#5757](https://github.com/sebastianbergmann/phpunit/issues/5757): Add assertions for ignoring whitespace differences in strings
* [#5810](https://github.com/sebastianbergmann/phpunit/issues/5810): Do not dump arrays and objects in failure messages of `IsTrue`, `IsFalse`, `IsNull`, `IsFinite`, `IsInfinite`, and `IsNan` constraints
* [#5838](https://github.com/sebastianbergmann/phpunit/issues/5838): Inherit `#[RunTestsInSeparateProcesses]` from parent test classes
* [#5922](https://github.com/sebastianbergmann/phpunit/issues/5922): `assertContainsEquals()` should use `sebastian/comparator` for element comparison
* [#6000](https://github.com/sebastianbergmann/phpunit/issues/6000): Report PHPT test as risky when `--SKIPIF--` does not have standard-output side-effect
* [#6075](https://github.com/sebastianbergmann/phpunit/issues/6075): Support test execution order sorted by descending duration
* [#6346](https://github.com/sebastianbergmann/phpunit/issues/6346): Emit warning when conflicting CLI options are used
* [#6534](https://github.com/sebastianbergmann/phpunit/issues/6534): Make `$_dataName` available to `#[TestDoxFormatter]` callbacks
* [#6559](https://github.com/sebastianbergmann/phpunit/issues/6559): Improved API for exception message expectations
* [#6565](https://github.com/sebastianbergmann/phpunit/pull/6565): Optional `$skipWhenEmpty` parameter for `#[DataProvider]` and `#[DataProviderExternal]`
* [#6566](https://github.com/sebastianbergmann/phpunit/pull/6566): Allow `--stop-on-defect`, `--stop-on-error`, etc. to accept an optional threshold
* [#6567](https://github.com/sebastianbergmann/phpunit/issues/6567): Make diff context lines configurable
* [#6574](https://github.com/sebastianbergmann/phpunit/issues/6574): Improve `willReturnMap()` with constraint support and strict matching
* [#6575](https://github.com/sebastianbergmann/phpunit/issues/6575): `--list-test-ids` CLI option and enhance `--filter` CLI option to support test ID syntax
* [#6577](https://github.com/sebastianbergmann/phpunit/issues/6577): `--run-test-id <test-id>` CLI option that accepts a single test ID for exact matching
* [#6579](https://github.com/sebastianbergmann/phpunit/pull/6579): Properly handle issues triggered outside of tests
* [#6597](https://github.com/sebastianbergmann/phpunit/pull/6597): Compact output (activated through `--compact` CLI option and `PHPUNIT_COMPACT_OUTPUT=1` environment variable)
* The `executionOrder` attribute in the XML configuration file now accepts `defects` combined with any main order, as well as three-way combinations of `depends`/`no-depends`, `defects`, and a main order (for example, `depends,defects,duration-ascending`)
* `--validate-configuration` CLI option to validate an XML configuration file for PHPUnit

### Changed

* [#5873](https://github.com/sebastianbergmann/phpunit/issues/5873): Chain previously registered error handler instead of silently disabling PHPUnit's error handling
* [#6535](https://github.com/sebastianbergmann/phpunit/pull/6535): Use `sebastian/file-filter` in `SourceFilter::includes()` for issue trigger identification
* [#6581](https://github.com/sebastianbergmann/phpunit/issues/6581): Allow `#[IgnoreDeprecations]` to be repeated
* Only errors and failures are now considered for "defect first" test reordering (tests that triggered deprecations, notices, or warnings as well as incomplete, risky, and skipped tests were previous also considered)

### Deprecated

* [#6075](https://github.com/sebastianbergmann/phpunit/issues/6075): `--order-by duration` CLI option, use `--order-by duration-ascending` instead
* [#6075](https://github.com/sebastianbergmann/phpunit/issues/6075): `--order-by size` CLI option, use `--order-by size-ascending` instead
* [#6075](https://github.com/sebastianbergmann/phpunit/issues/6075): `executionOrder="duration"` XML configuration attribute value, use `executionOrder="duration-ascending"` instead
* [#6075](https://github.com/sebastianbergmann/phpunit/issues/6075): `executionOrder="size"` XML configuration attribute value, use `executionOrder="size-ascending"` instead
* [#6560](https://github.com/sebastianbergmann/phpunit/issues/6560): Soft-deprecate `expectExceptionMessage()`, use `expectExceptionMessageIsOrContains()` instead

### Fixed

* [#5845](https://github.com/sebastianbergmann/phpunit/issues/5845): Error handlers registered before PHPUnit (e.g. via `auto_prepend_file`) cause false "risky test" warnings
* [#5851](https://github.com/sebastianbergmann/phpunit/issues/5851): Output buffer manipulation in tests causes incorrect capture, hangs, and silent failures
* [#6582](https://github.com/sebastianbergmann/phpunit/issues/6582): `TestSuiteSorter::cmpSize()` does not handle `TestSuite` objects for `TestCase` classes

[13.2.0]: https://github.com/sebastianbergmann/phpunit/compare/13.1...main
