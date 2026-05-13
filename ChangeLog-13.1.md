# Changes in PHPUnit 13.1

All notable changes of the PHPUnit 13.1 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.1.9] - 2026-05-13

### Changed

* A `Test` or `Tests` prefix is no longer stripped from class names when they are processed for TestDox output

### Fixed

* [#6605](https://github.com/sebastianbergmann/phpunit/issues/6605): Data set names and provider values containing Unicode bidirectional control characters distort terminal output
* [#6610](https://github.com/sebastianbergmann/phpunit/issues/6610): Per-testsuite bootstrap script not loaded in process isolation
* TestDox output collapsed separate test classes into a single group when their prettified class names matched

## [13.1.8] - 2026-05-01

### Fixed

* [#6595](https://github.com/sebastianbergmann/phpunit/issues/6595): Crash when before-class or after-class method fails with assertion failure
* [#6599](https://github.com/sebastianbergmann/phpunit/issues/6599): TeamCity logger does not wrap failures in before-test methods with `testStarted` and `testFinished`
* [#6601](https://github.com/sebastianbergmann/phpunit/issues/6601): Anonymous classes are not rejected with a clear error when creating a test double
* [#6603](https://github.com/sebastianbergmann/phpunit/issues/6603): `assertArrays*IgnoringOrder()` fails on mixed scalar types and on reordered nested associative arrays
* `MockBuilder::setMockClassName()` and `TestStubBuilder::setStubClassName()` now reject values that are not valid unqualified PHP class identifiers, throwing the new `InvalidClassNameException`
* The regular expression used by `Generator::ensureValidMethods()` to validate method names passed to `MockBuilder::onlyMethods()` and `addMethods()` was not anchored, so any string containing a valid identifier substring (including strings with parentheses, braces, comments, or newlines) was accepted

## [13.1.7] - 2026-04-18

### Changed

* Pass `LIBXML_NONET` when parsing/validating XML configuration files to make explicit that no network I/O is performed
* Verify the result file written by an isolated child process with a random nonce before deserializing it

## [13.1.6] - 2026-04-17

### Fixed

* [#6590](https://github.com/sebastianbergmann/phpunit/issues/6590): Silent failure when configuration file is invalid
* [#6592](https://github.com/sebastianbergmann/phpunit/pull/6592): INI metacharacters `;` and `"` are not preserved when forwarding settings to child processes

## [13.1.5] - 2026-04-16

### Fixed

* [#5860](https://github.com/sebastianbergmann/phpunit/issues/5860): PHP CLI `-d` settings are not forwarded to child processes for process isolation
* [#6451](https://github.com/sebastianbergmann/phpunit/issues/6451): Incomplete version in `RequiresPhp` (e.g. `<=8.5`) is compared against full PHP version, causing unexpected skips
* [#6589](https://github.com/sebastianbergmann/phpunit/issues/6589): `dataSetAsStringWithData()` raises "float is not representable as int" warning for large floats in data sets

## [13.1.4] - 2026-04-15

### Fixed

* [#5993](https://github.com/sebastianbergmann/phpunit/issues/5993): `DefaultJobRunner` deadlocks on child processes that write large amounts of stderr output
* [#6465](https://github.com/sebastianbergmann/phpunit/issues/6465): SAPI-populated `$_SERVER` entries leak from parent into child process
* [#6587](https://github.com/sebastianbergmann/phpunit/issues/6587): `failOnEmptyTestSuite="false"` in `phpunit.xml` is ignored when `--group`/`--filter`/`--testsuite` matches no tests
* [#6588](https://github.com/sebastianbergmann/phpunit/issues/6588): Order of issue baseline entries is not canonicalized

## [13.1.3] - 2026-04-13

### Fixed

* Regression in XML configuration migration introduced in PHPUnit 12.5.8

## [13.1.2] - 2026-04-13

### Fixed

* [#4571](https://github.com/sebastianbergmann/phpunit/issues/4571): No warning when `--random-order-seed` is used when test execution order is not random
* [#4975](https://github.com/sebastianbergmann/phpunit/issues/4975): `--filter` does not work when filter string starts with `#`
* [#5354](https://github.com/sebastianbergmann/phpunit/issues/5354): JUnit XML logger does not handle `TestSuiteSkipped` event
* [#6276](https://github.com/sebastianbergmann/phpunit/issues/6276): Exit with non-zero exit code when explicit test selection (`--filter`, `--group`, `--testsuite`) yields no tests
* [#6583](https://github.com/sebastianbergmann/phpunit/issues/6583): Failing output expectation skips `tearDown()` and handler restoration, causing subsequent tests to be marked as risky

## [13.1.1] - 2026-04-08

### Changed

* [#3676](https://github.com/sebastianbergmann/phpunit/issues/3676): Include class/interface name in mock object expectation failure messages
* [#4793](https://github.com/sebastianbergmann/phpunit/issues/4793): Exit with non-zero exit code when `exit` was called from some test

### Fixed

* [#5881](https://github.com/sebastianbergmann/phpunit/issues/5881): `colors="true"` in XML configuration file does not unconditionally enable colored output
* [#6019](https://github.com/sebastianbergmann/phpunit/issues/6019): `--migrate-configuration` does not update schema location when XML content already validates against current schema
* [#6372](https://github.com/sebastianbergmann/phpunit/issues/6372): Assertion failure inside `willReturnCallback()` is silently swallowed when code under test catches exceptions
* [#6464](https://github.com/sebastianbergmann/phpunit/issues/6464): Process isolation template unconditionally calls `set_include_path()`
* [#6571](https://github.com/sebastianbergmann/phpunit/issues/6571): Static analysis errors for `TestDoubleBuilder` method chaining

## [13.1.0] - 2026-04-03

### Added

* [#6501](https://github.com/sebastianbergmann/phpunit/issues/6501): Include unexpected output in Open Test Reporting (OTR) XML logfile
* [#6517](https://github.com/sebastianbergmann/phpunit/issues/6517): `includeInCodeCoverage` attribute for `<directory>` and `<file>` children of `<source>`
* [#6523](https://github.com/sebastianbergmann/phpunit/issues/6523): Include `#[Group]` information in Open Test Reporting (OTR) XML logfile
* [#6524](https://github.com/sebastianbergmann/phpunit/pull/6524): Report issues in Open Test Reporting (OTR) XML logfile
* [#6526](https://github.com/sebastianbergmann/phpunit/pull/6526): Introduce `#[DataProviderClosure]` for static closures
* [#6530](https://github.com/sebastianbergmann/phpunit/issues/6530): Support for custom issue trigger resolvers that can be configured using `<issueTriggerResolvers>` in the XML configuration file
* [#6547](https://github.com/sebastianbergmann/phpunit/pull/6547): Support for `%r...%r` in `EXPECTF` section
* Support for configuring HTML code coverage report options (colors, thresholds, custom CSS) in XML configuration file without requiring an `outputDirectory` attribute, allowing the output directory to be specified later with the `--coverage-html` CLI option
* Support for configuring dark mode colors, progress bar colors, and breadcrumb colors for HTML code coverage reports in the XML configuration file

### Changed

* [#6557](https://github.com/sebastianbergmann/phpunit/pull/6557): Improve failure description for `StringMatchesFormatDescription` constraint which is used by `assertFileMatchesFormat()`, `assertFileMatchesFormatFile()`, `assertStringMatchesFormat()`, `assertStringMatchesFormatFile()`, and `EXPECTF` sections of PHPT test files
* The HTML code coverage report now uses a more colorblind-friendly blue/amber/orange palette by default
* Extracted `PHPUnit\Runner\Extension\Facade` from a concrete class to an interface and introduced an internal `ExtensionFacade` implementation, so that extensions only depend on the `Facade` interface while PHPUnit internally uses the `ExtensionFacade` class that also provides query methods

### Deprecated

* [#6515](https://github.com/sebastianbergmann/phpunit/issues/6515): Deprecate the `--log-events-verbose-text <file>` CLI option
* [#6537](https://github.com/sebastianbergmann/phpunit/issues/6537): Soft-deprecate `id()` and `after()` for mock object expectations

### Fixed

* [#6025](https://github.com/sebastianbergmann/phpunit/issues/6025): `FILE_EXTERNAL` breaks `__DIR__`
* [#6351](https://github.com/sebastianbergmann/phpunit/issues/6351): No warning when the same test runner extension is configured more than once
* [#6433](https://github.com/sebastianbergmann/phpunit/issues/6433): Logic in `TestSuiteLoader` is brittle and causes "Class FooTest not found" even for valid tests in valid filenames
* [#6463](https://github.com/sebastianbergmann/phpunit/issues/6463): Process Isolation fails on non-serializable globals and quietly ignore closures

[13.1.9]: https://github.com/sebastianbergmann/phpunit/compare/13.1.8...13.1.9
[13.1.8]: https://github.com/sebastianbergmann/phpunit/compare/13.1.7...13.1.8
[13.1.7]: https://github.com/sebastianbergmann/phpunit/compare/13.1.6...13.1.7
[13.1.6]: https://github.com/sebastianbergmann/phpunit/compare/13.1.5...13.1.6
[13.1.5]: https://github.com/sebastianbergmann/phpunit/compare/13.1.4...13.1.5
[13.1.4]: https://github.com/sebastianbergmann/phpunit/compare/13.1.3...13.1.4
[13.1.3]: https://github.com/sebastianbergmann/phpunit/compare/13.1.2...13.1.3
[13.1.2]: https://github.com/sebastianbergmann/phpunit/compare/13.1.1...13.1.2
[13.1.1]: https://github.com/sebastianbergmann/phpunit/compare/13.1.0...13.1.1
[13.1.0]: https://github.com/sebastianbergmann/phpunit/compare/13.0.6...13.1.0
