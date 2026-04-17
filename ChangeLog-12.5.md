# Changes in PHPUnit 12.5

All notable changes of the PHPUnit 12.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.5.22] - 2026-04-17

### Fixed

* [#6590](https://github.com/sebastianbergmann/phpunit/issues/6590): Silent failure when configuration file is invalid
* [#6592](https://github.com/sebastianbergmann/phpunit/pull/6592): INI metacharacters `;` and `"` are not preserved when forwarding settings to child processes

## [12.5.21] - 2026-04-16

### Fixed

* [#5860](https://github.com/sebastianbergmann/phpunit/issues/5860): PHP CLI `-d` settings are not forwarded to child processes for process isolation
* [#6451](https://github.com/sebastianbergmann/phpunit/issues/6451): Incomplete version in `RequiresPhp` (e.g. `<=8.5`) is compared against full PHP version, causing unexpected skips

## [12.5.20] - 2026-04-15

### Fixed

* [#5993](https://github.com/sebastianbergmann/phpunit/issues/5993): `DefaultJobRunner` deadlocks on child processes that write large amounts of stderr output
* [#6465](https://github.com/sebastianbergmann/phpunit/issues/6465): SAPI-populated `$_SERVER` entries leak from parent into child process
* [#6587](https://github.com/sebastianbergmann/phpunit/issues/6587): `failOnEmptyTestSuite="false"` in `phpunit.xml` is ignored when `--group`/`--filter`/`--testsuite` matches no tests
* [#6588](https://github.com/sebastianbergmann/phpunit/issues/6588): Order of issue baseline entries is not canonicalized

## [12.5.19] - 2026-04-13

### Fixed

* Regression in XML configuration migration introduced in PHPUnit 12.5.8

## [12.5.18] - 2026-04-13

### Fixed

* [#4571](https://github.com/sebastianbergmann/phpunit/issues/4571): No warning when `--random-order-seed` is used when test execution order is not random
* [#4975](https://github.com/sebastianbergmann/phpunit/issues/4975): `--filter` does not work when filter string starts with `#`
* [#5354](https://github.com/sebastianbergmann/phpunit/issues/5354): JUnit XML logger does not handle `TestSuiteSkipped` event
* [#6276](https://github.com/sebastianbergmann/phpunit/issues/6276): Exit with non-zero exit code when explicit test selection (`--filter`, `--group`, `--testsuite`) yields no tests
* [#6583](https://github.com/sebastianbergmann/phpunit/issues/6583): Failing output expectation skips `tearDown()` and handler restoration, causing subsequent tests to be marked as risky

## [12.5.17] - 2026-04-08

### Changed
 
* [#4793](https://github.com/sebastianbergmann/phpunit/issues/4793): Exit with non-zero exit code when `exit` was called from some test

### Fixed

* [#5881](https://github.com/sebastianbergmann/phpunit/issues/5881): `colors="true"` in XML configuration file does not unconditionally enable colored output
* [#6019](https://github.com/sebastianbergmann/phpunit/issues/6019): `--migrate-configuration` does not update schema location when XML content already validates against current schema
* [#6372](https://github.com/sebastianbergmann/phpunit/issues/6372): Assertion failure inside `willReturnCallback()` is silently swallowed when code under test catches exceptions
* [#6464](https://github.com/sebastianbergmann/phpunit/issues/6464): Process isolation template unconditionally calls `set_include_path()`
* [#6571](https://github.com/sebastianbergmann/phpunit/issues/6571): Static analysis errors for `TestDoubleBuilder` method chaining

## [12.5.16] - 2026-04-03

### Added

* [#6547](https://github.com/sebastianbergmann/phpunit/pull/6547): Support for `%r...%r` in `EXPECTF` section

### Fixed

* [#6025](https://github.com/sebastianbergmann/phpunit/issues/6025): `FILE_EXTERNAL` breaks `__DIR__`
* [#6351](https://github.com/sebastianbergmann/phpunit/issues/6351): No warning when the same test runner extension is configured more than once
* [#6433](https://github.com/sebastianbergmann/phpunit/issues/6433): Logic in `TestSuiteLoader` is brittle and causes "Class FooTest not found" even for valid tests in valid filenames
* [#6463](https://github.com/sebastianbergmann/phpunit/issues/6463): Process Isolation fails on non-serializable globals and quietly ignore closures

## [12.5.15] - 2026-03-31

### Changed

* [#4440](https://github.com/sebastianbergmann/phpunit/issues/4440): Improve error when configured code coverage file list is empty
* [#6549](https://github.com/sebastianbergmann/phpunit/pull/6549): Allow to stub both hooks of non-virtual properties

### Fixed

* [#6529](https://github.com/sebastianbergmann/phpunit/pull/6529): Git "detached HEAD state" in Open Test Reporting (OTR) XML logger not handled properly
* [#6545](https://github.com/sebastianbergmann/phpunit/issues/6545): Stubbing a class with set property hook leaves property uninitialized by default
* The `RegularExpression` and `StringMatchesFormatDescription` did not handle `preg_match()` errors such as `Compilation failed: regular expression is too large`

## [12.5.14] - 2026-02-18

### Fixed

* [#6521](https://github.com/sebastianbergmann/phpunit/issues/6521): Performance regression in PHPUnit 11.5.54, PHPUnit 12.5.13, and PHPUnit 13.0.4

## [12.5.13] - 2026-02-18

### Fixed

* [#6489](https://github.com/sebastianbergmann/phpunit/pull/6489): Classification of self/direct/indirect deprecation triggers is not aligned with Symfony's bridge for PHPUnit

## [12.5.12] - 2026-02-16

### Fixed

* [#6511](https://github.com/sebastianbergmann/phpunit/issues/6511): TestDox variables out of order with named arguments
* [#6514](https://github.com/sebastianbergmann/phpunit/issues/6514): `<ini />` can silently fail

## [12.5.11] - 2026-02-10

### Deprecated

* [#6510](https://github.com/sebastianbergmann/phpunit/issues/6510): Deprecate using `with*()` on test stubs

### Fixed

* [#6503](https://github.com/sebastianbergmann/phpunit/issues/6503): Temporary file used by `SourceMapper` may be deleted prematurely when multiple PHPUnit processes run in parallel
* [#6509](https://github.com/sebastianbergmann/phpunit/issues/6509): "No expectations were configured for the mock object ..." notice is emitted when `with()` is used without `expects()`

## [12.5.10] - 2026-02-08

### Fixed

* [#6495](https://github.com/sebastianbergmann/phpunit/pull/6495): Source map for issue trigger identification is regenerated in process isolation child processes

## [12.5.9] - 2026-02-05

### Added

* [#6488](https://github.com/sebastianbergmann/phpunit/issues/6488): Allow disabling issue trigger identification for improved performance

### Fixed

* [#6486](https://github.com/sebastianbergmann/phpunit/issues/6486): Incorrect file name reported for errors for test methods declared in traits
* [#6490](https://github.com/sebastianbergmann/phpunit/pull/6490): Incorrect test count when tests are skipped in before-class method

## [12.5.8] - 2026-01-27

### Changed

* To prevent Poisoned Pipeline Execution (PPE) attacks using prepared `.coverage` files in pull requests, a PHPT test will no longer be run if the temporary file for writing code coverage information already exists before the test runs

## [12.5.7] - 2026-01-24

### Fixed

* [#6362](https://github.com/sebastianbergmann/phpunit/issues/6362): Manually instantiated test doubles are broken since PHPUnit 11.2
* [#6470](https://github.com/sebastianbergmann/phpunit/issues/6470): Infinite recursion in `Count::getCountOf()` for unusal implementations of `Iterator` or `IteratorAggregate`

## [12.5.6] - 2026-01-16

### Changed

* Reverted a change that caused a [build failure](https://github.com/php/php-src/actions/runs/21052584327/job/60542023395#step:14:3729) for the [PHP project's nightly community job](https://phpunit.expert/articles/how-php-and-its-ecosystem-test-each-other.html?ref=github)

## [12.5.5] - 2026-01-15

### Changed

* `PHPUnit\Framework\MockObject` exceptions are now subtypes of `PHPUnit\Exception`

### Deprecated

* [#6461](https://github.com/sebastianbergmann/phpunit/issues/6461): `any()` matcher (soft deprecation)

### Fixed

* [#6470](https://github.com/sebastianbergmann/phpunit/issues/6470): Mocking a class with a property hook setter accepting more types than the property results in a fatal error

## [12.5.4] - 2025-12-15

### Changed

* The `#[AllowMockObjectsWithoutExpectations]` attribute can now be used on the method level

### Fixed

* [#6446](https://github.com/sebastianbergmann/phpunit/issues/6446): Test runner crashes with `Timer::start() has to be called before Timer::stop()`

## [12.5.3] - 2025-12-11

### Changed

* The message emitted when a test method creates a mock object but does not configure any expectations for it has been improved

## [12.5.2] - 2025-12-08

### Added

* Attribute `#[AllowMockObjectsWithoutExpectations]` for excluding tests from the check that emits the notice for test methods that create a mock object but do not configure an expectation for it

## [12.5.1] - 2025-12-06

### Added

* `TestCase::getStubBuilder()` (analogous to `TestCase::getMockBuilder()`) for creating (partial) test stubs using a fluent API

## [12.5.0] - 2025-12-05

### Added

* [#6376](https://github.com/sebastianbergmann/phpunit/issues/6376): `--all` CLI option to ignore test selection configured in XML configuration file
* [#6422](https://github.com/sebastianbergmann/phpunit/issues/6422): Make `<source>` element in XML code coverage report optional

### Changed

* [#6380](https://github.com/sebastianbergmann/phpunit/pull/6380): Allow `Throwable` in `expectExceptionObject()`
* A PHPUnit notice is now emitted for test methods that create a mock object but do not configure an expectation for it

[12.5.22]: https://github.com/sebastianbergmann/phpunit/compare/12.5.21...12.5.22
[12.5.21]: https://github.com/sebastianbergmann/phpunit/compare/12.5.20...12.5.21
[12.5.20]: https://github.com/sebastianbergmann/phpunit/compare/12.5.19...12.5.20
[12.5.19]: https://github.com/sebastianbergmann/phpunit/compare/12.5.18...12.5.19
[12.5.18]: https://github.com/sebastianbergmann/phpunit/compare/12.5.17...12.5.18
[12.5.17]: https://github.com/sebastianbergmann/phpunit/compare/12.5.16...12.5.17
[12.5.16]: https://github.com/sebastianbergmann/phpunit/compare/12.5.15...12.5.16
[12.5.15]: https://github.com/sebastianbergmann/phpunit/compare/12.5.14...12.5.15
[12.5.14]: https://github.com/sebastianbergmann/phpunit/compare/12.5.13...12.5.14
[12.5.13]: https://github.com/sebastianbergmann/phpunit/compare/12.5.12...12.5.13
[12.5.12]: https://github.com/sebastianbergmann/phpunit/compare/12.5.11...12.5.12
[12.5.11]: https://github.com/sebastianbergmann/phpunit/compare/12.5.10...12.5.11
[12.5.10]: https://github.com/sebastianbergmann/phpunit/compare/12.5.9...12.5.10
[12.5.9]: https://github.com/sebastianbergmann/phpunit/compare/12.5.8...12.5.9
[12.5.8]: https://github.com/sebastianbergmann/phpunit/compare/12.5.7...12.5.8
[12.5.7]: https://github.com/sebastianbergmann/phpunit/compare/12.5.6...12.5.7
[12.5.6]: https://github.com/sebastianbergmann/phpunit/compare/12.5.5...12.5.6
[12.5.5]: https://github.com/sebastianbergmann/phpunit/compare/12.5.4...12.5.5
[12.5.4]: https://github.com/sebastianbergmann/phpunit/compare/12.5.3...12.5.4
[12.5.3]: https://github.com/sebastianbergmann/phpunit/compare/12.5.2...12.5.3
[12.5.2]: https://github.com/sebastianbergmann/phpunit/compare/12.5.1...12.5.2
[12.5.1]: https://github.com/sebastianbergmann/phpunit/compare/12.5.0...12.5.1
[12.5.0]: https://github.com/sebastianbergmann/phpunit/compare/12.4.5...12.5.0
