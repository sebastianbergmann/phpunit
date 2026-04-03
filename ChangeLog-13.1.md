# Changes in PHPUnit 13.1

All notable changes of the PHPUnit 13.1 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

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

[13.1.0]: https://github.com/sebastianbergmann/phpunit/compare/13.0.6...13.1.0
