# Changes in PHPUnit 12.0

All notable changes of the PHPUnit 12.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.0.7] - 2025-03-07

### Fixed

* [#5976](https://github.com/sebastianbergmann/phpunit/issues/5976): TestDox result printer does not display details about errors triggered in before-first-test and after-last-test methods

## [12.0.6] - 2025-03-05

### Fixed

* [#6142](https://github.com/sebastianbergmann/phpunit/issues/6142): `$expected` and `$actual` are mixed up in failure description when `assertJsonFileEqualsJsonFile()` fails

## [12.0.5] - 2025-02-25

### Fixed

* [#6138](https://github.com/sebastianbergmann/phpunit/issues/6138): Test with failed expectation on value passed to mocked method is incorrectly considered risky

## [12.0.4] - 2025-02-21

### Fixed

* [#6134](https://github.com/sebastianbergmann/phpunit/issues/6134): Missing event when child process ends unexpectedly

## [12.0.3] - 2025-02-18

### Changed

* `TestCase::__construct()` is now declared `final` (it was annotated with `@final` before and the announced changed from `@final` to `final` for PHPUnit 12 was forgotten)

### Fixed

* [#5951](https://github.com/sebastianbergmann/phpunit/issues/5951#issuecomment-2656364815): Restore the `includeUncoveredFiles` configuration option
* [#6133](https://github.com/sebastianbergmann/phpunit/issues/6133): Precision loss in aggregated test suite execution time(s) reported by JUnit XML logger
* A `Test\PreparationFailed` event is now emitted in addition to a `Test\Errored` event when an unexpected exception is triggered in a before-test method
* A `Test\Passed` event is no longer emitted in addition to a `Test\Failed` or `Test\Errored` event when an assertion failure or an unexpected exception is triggered in an after-test method
* A `TestSuite\Finished` event is now emitted when a before-first-test method errors

## [12.0.2] - 2025-02-08

### Changed

* Updated dependencies for PHAR distribution

## [12.0.1] - 2025-02-07

### Fixed

* Deprecation message for `Assert::isType()`, `assertContainsOnly()`, `assertNotContainsOnly()`, and `containsOnly()`

## [12.0.0] - 2025-02-07

### Added

* [#5984](https://github.com/sebastianbergmann/phpunit/issues/5984): `#[CoversClassesThatExtendClass]` and `#[UsesClassesThatExtendClass]`
* [#5985](https://github.com/sebastianbergmann/phpunit/issues/5985): `#[CoversClassesThatImplementInterface]` and `#[UsesClassesThatImplementInterface]`
* [#6073](https://github.com/sebastianbergmann/phpunit/issues/6073): `#[CoversNamespace]` and `#[UsesNamespace]`
* [#6074](https://github.com/sebastianbergmann/phpunit/pull/6074): `#[RequiresEnvironmentVariable]`

### Changed

* [#5872](https://github.com/sebastianbergmann/phpunit/issues/5872): The default value for `shortenArraysForExportThreshold` is now `10` (limit export of arrays to 10 levels) instead of `0` (do not limit export of arrays)

### Deprecated

* [#6053](https://github.com/sebastianbergmann/phpunit/issues/6053): `Assert::isType()` (was soft-deprecated in PHPUnit 11.5)
* [#6056](https://github.com/sebastianbergmann/phpunit/issues/6056): `assertContainsOnly()` (was soft-deprecated in PHPUnit 11.5)
* [#6056](https://github.com/sebastianbergmann/phpunit/issues/6056): `assertNotContainsOnly()` (was soft-deprecated in PHPUnit 11.5)
* [#6060](https://github.com/sebastianbergmann/phpunit/issues/6060): `containsOnly()` (was soft-deprecated in PHPUnit 11.5)

### Removed

* [#5215](https://github.com/sebastianbergmann/phpunit/issues/5215): `TestCase::iniSet()`
* [#5217](https://github.com/sebastianbergmann/phpunit/issues/5217): `TestCase::setLocale()`
* [#5246](https://github.com/sebastianbergmann/phpunit/issues/5246): `TestCase::createTestProxy()`
* [#5247](https://github.com/sebastianbergmann/phpunit/issues/5247): `TestCase::getMockForAbstractClass()`
* [#5248](https://github.com/sebastianbergmann/phpunit/issues/5248): `TestCase::getMockFromWsdl()`
* [#5249](https://github.com/sebastianbergmann/phpunit/issues/5249): `TestCase::getMockForTrait()`
* [#5250](https://github.com/sebastianbergmann/phpunit/issues/5250): `TestCase::getObjectForTrait()`
* [#5310](https://github.com/sebastianbergmann/phpunit/issues/5310): `MockBuilder::enableAutoload()` and `MockBuilder::disableAutoload()`
* [#5311](https://github.com/sebastianbergmann/phpunit/issues/5311): `MockBuilder::allowMockingUnknownTypes()` and `MockBuilder::disallowMockingUnknownTypes()`
* [#5312](https://github.com/sebastianbergmann/phpunit/issues/5312): `MockBuilder::enableProxyingToOriginalMethods()`, `MockBuilder::disableProxyingToOriginalMethods()`, and `MockBuilder::setProxyTarget()`
* [#5313](https://github.com/sebastianbergmann/phpunit/issues/5313): `MockBuilder::getMockForTrait()`
* [#5314](https://github.com/sebastianbergmann/phpunit/issues/5314): `MockBuilder::getMockForAbstractClass()`
* [#5316](https://github.com/sebastianbergmann/phpunit/issues/5316): `MockBuilder::enableArgumentCloning()` and `MockBuilder::disableArgumentCloning()`
* [#5321](https://github.com/sebastianbergmann/phpunit/issues/5321): `MockBuilder::addMethods()`
* [#5416](https://github.com/sebastianbergmann/phpunit/issues/5416): Support for doubling interfaces (or classes) that have a method named `method`
* [#5424](https://github.com/sebastianbergmann/phpunit/issues/5424): `TestCase` methods for creating return stub configuration objects
* [#5473](https://github.com/sebastianbergmann/phpunit/issues/5473): `assertStringNotMatchesFormat()` and `assertStringNotMatchesFormatFile()`
* [#5536](https://github.com/sebastianbergmann/phpunit/issues/5536): Support for configuring expectations using `expects()` on test stubs
* [#5541](https://github.com/sebastianbergmann/phpunit/issues/5541): Support for metadata in doc-comments
* [#5710](https://github.com/sebastianbergmann/phpunit/issues/5710): Support for using comma-separated values with the `--group`, `--exclude-group`, `--covers`, `--uses`, and `--test-suffix` CLI options
* [#5756](https://github.com/sebastianbergmann/phpunit/issues/5756): Support for the `restrictDeprecations` attribute on the `<source>` element of the XML configuration file
* [#5801](https://github.com/sebastianbergmann/phpunit/issues/5801): Support for targeting traits with `#[CoversClass]` and `#[UsesClass]` attributes
* [#5978](https://github.com/sebastianbergmann/phpunit/issues/5978): Support for PHP 8.2

[12.0.7]: https://github.com/sebastianbergmann/phpunit/compare/12.0.6...12.0.7
[12.0.6]: https://github.com/sebastianbergmann/phpunit/compare/12.0.5...12.0.6
[12.0.5]: https://github.com/sebastianbergmann/phpunit/compare/12.0.4...12.0.5
[12.0.4]: https://github.com/sebastianbergmann/phpunit/compare/12.0.3...12.0.4
[12.0.3]: https://github.com/sebastianbergmann/phpunit/compare/12.0.2...12.0.3
[12.0.2]: https://github.com/sebastianbergmann/phpunit/compare/12.0.1...12.0.2
[12.0.1]: https://github.com/sebastianbergmann/phpunit/compare/12.0.0...12.0.1
[12.0.0]: https://github.com/sebastianbergmann/phpunit/compare/11.5...12.0.0
