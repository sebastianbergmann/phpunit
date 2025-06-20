# Changes in PHPUnit 12.2

All notable changes of the PHPUnit 12.2 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.2.3] - 2025-06-20

### Added

* [#6236](https://github.com/sebastianbergmann/phpunit/issues/6236): `failOnPhpunitWarning` attribute on the `<phpunit>` element of the XML configuration file and `--fail-on-phpunit-warning` CLI option for controlling whether PHPUnit should fail on PHPUnit warnings (default: `true`)
* [#6239](https://github.com/sebastianbergmann/phpunit/issues/6239): `--do-not-fail-on-deprecation`, `--do-not-fail-on-phpunit-warning`, `--do-not-fail-on-phpunit-deprecation`, `--do-not-fail-on-empty-test-suite`, `--do-not-fail-on-incomplete`, `--do-not-fail-on-notice`, `--do-not-fail-on-risky`, `--do-not-fail-on-skipped`, and `--do-not-fail-on-warning` CLI options
* `--do-not-report-useless-tests` CLI option as a replacement for `--dont-report-useless-tests`

### Deprecated

* `--dont-report-useless-tests` CLI option (use `--do-not-report-useless-tests` instead)

### Fixed

* [#6243](https://github.com/sebastianbergmann/phpunit/issues/6243): Constraints cannot be implemented without using internal class `ExpectationFailedException`

## [12.2.2] - 2025-06-13

### Fixed

* [#6222](https://github.com/sebastianbergmann/phpunit/issues/6222): Data Provider seems to mess up Test Dependencies
* `shortenArraysForExportThreshold` XML configuration setting has no effect on all arrays exported for event-related value objects

## [12.2.1] - 2025-06-07

### Fixed

* [#6228](https://github.com/sebastianbergmann/phpunit/pull/6228): Variadic test methods should not warn about too many arguments from data provider

## [12.2.0] - 2025-06-06

### Added

#### Experimental Support for Open Test Reporting XML

PHPUnit has supported reporting test results in the JUnit XML format for a long time. Unfortunately, there has never been a standard schema for the JUnit XML format. Common consumers of Clover XML log files interpret these files differently, which has led to frequent problems.

To address this, the JUnit team started the [Open Test Reporting project](https://github.com/ota4j-team/open-test-reporting), creating and maintaining language-agnostic XML and HTML test reporting formats. Unlike JUnit XML, Open Test Reporting's XML formats are extensible.

Logging in the Open Test Reporting XML format is controlled by the new `--log-otr` CLI option and the new `<otr>` XML configuration element.

This feature is experimental and the generated XML may change in order to enhance compliance with the Open Test Reporting project's XML schema definitions. The same applies to the XML schema definitions for information that is specific for PHP and PHPUnit. Please note that such changes may occur in bugfix or minor releases and could potentially break backwards compatibility.

#### Experimental Support for OpenClover XML

PHPUnit has supported reporting code coverage information in the Clover XML format for a long time. Unfortunately, there has never been a standard schema for the Clover XML format. Common consumers of Clover XML log files interpret these files differently, which leads to frequent problems.

The original commercial Clover project has been superseded by the Open Source OpenClover project, which provides an XML schema for its OpenClover XML format. Rather than modifying the existing Clover XML reporter to comply with the OpenClover XML schema, thereby breaking backward compatibility, a new OpenClover XML reporter has been introduced.

This new reporter is controlled by the new CLI option, `--coverage-openclover`, and the new XML configuration element, `<openclover>`. This code coverage reporter generates XML documents that validate against the OpenClover project's XML schema definition, with one exception: the `<testproject>` element is not generated.

The existing Clover XML reporter, controlled by the `--coverage-clover` CLI option and the `<clover>` XML configuration element, remains unchanged.

This feature is experimental and the generated XML may change to enhance compliance with the OpenClover XML schema definition. Please note that such changes may occur in bugfix or minor releases and could potentially break backwards compatibility.

#### Miscellaneous

* `--with-telemetry` CLI option that can be used together with `--debug` to print debugging information that includes telemetry information
* The `TestCase::provideAdditionalInformation()` method can now be used to emit a `Test\AdditionalInformationProvided` event
* The new `Test\AfterLastTestMethodFailed`, `Test\AfterTestMethodFailed`, `Test\BeforeFirstTestMethodFailed`, `Test\BeforeTestMethodFailed`, `Test\PostConditionFailed`, `Test\PreConditionFailed` events are now emitted instead of `Test\AfterLastTestMethodErrored`, `Test\AfterTestMethodErrored`, `Test\BeforeFirstTestMethodErrored`, `Test\BeforeTestMethodErrored`, `Test\PostConditionErrored`, `Test\PreConditionErrored` when the `Throwable` extends `AssertionFailedError` to distinguish between errors and failures triggered in hook methods
* The new `Test\PreparationErrored` event is now emitted instead of `Test\PreparationFailed` when the `Throwable` does not extend `AssertionFailedError` to distinguish between errors and failures triggered during test preparation
* `Test\PreparationFailed::throwable()`

### Changed

* [#6165](https://github.com/sebastianbergmann/phpunit/pull/6165): Collect deprecations triggered by autoloading while loading/building the test suite
* Do not treat warnings differently than other issues in summary section of default output
* A warning is now emitted when both `#[CoversNothing]` and `#[Covers*]` (or `#[Uses*]`) are used on a test class
* A warning is now emitted when the same `#[Covers*]` (or `#[Uses*]`) attribute is used multiple times on a test class
* A warning is now emitted when the same code is targeted by both `#[Covers*]` and `#[Uses*]` attributes
* A warning is now emitted when a hook method such as `setUp()`, for example has a `#[Test]` attribute
* A warning is now emitted when more than one of `#[Small]`, `#[Medium]`, or `#[Large]` is used on a test class
* A warning is now emitted when a data provider provides data sets that have more values than the test method consumes using arguments

[12.2.3]: https://github.com/sebastianbergmann/phpunit/compare/12.2.2...12.2.3
[12.2.2]: https://github.com/sebastianbergmann/phpunit/compare/12.2.1...12.2.2
[12.2.1]: https://github.com/sebastianbergmann/phpunit/compare/12.2.0...12.2.1
[12.2.0]: https://github.com/sebastianbergmann/phpunit/compare/12.1.6...12.2.0
