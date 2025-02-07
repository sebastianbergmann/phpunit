# Changes in PHPUnit 12.0

All notable changes of the PHPUnit 12.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

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

[12.0.0]: https://github.com/sebastianbergmann/phpunit/compare/11.5...12.0.0
