# Changes in PHPUnit 10.1

All notable changes of the PHPUnit 10.1 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [10.1.3] - 2023-05-11

### Changed

* [#5343](https://github.com/sebastianbergmann/phpunit/pull/5343): Provide distinct messages when a test is considered risky due to output buffering level mismatch

### Fixed

* [#5345](https://github.com/sebastianbergmann/phpunit/issues/5345): No stack trace shown for previous exceptions during bootstrap

## [10.1.2] - 2023-04-22

### Added

* `PHPUnit\Runner\Extension\Facade::replaceOutput()` and `PHPUnit\Runner\Extension\Facade::replacesOutput()`
* `PHPUnit\Event\Telemetry\Info::garbageCollectorStatus()`

### Fixed

* [#5340](https://github.com/sebastianbergmann/phpunit/issues/5340): Not all test-related events are emitted when a test fails or errors

## [10.1.1] - 2023-04-17

### Fixed

* Tests that have `#[DoesNotPerformAssertions]` (or `@doesNotPerformAssertions`) do not contribute to code coverage
* `#[DoesNotPerformAssertions]` (and `@doesNotPerformAssertions`) is only interpreted on the method level, not on the class level

## [10.1.0] - 2023-04-14

### Added

* [#5168](https://github.com/sebastianbergmann/phpunit/issues/5168): Allow test runner extensions to disable default progress and result printing
* [#5196](https://github.com/sebastianbergmann/phpunit/issues/5196): Optionally (fail|stop) on (notice|deprecation) events
* [#5201](https://github.com/sebastianbergmann/phpunit/issues/5201): Add `TestCase::registerFailureType()` to register interface that marks exceptions to be treated as failures, not errors
* [#5231](https://github.com/sebastianbergmann/phpunit/pull/5231): `assertObjectHasProperty()` and `assertObjectNotHasProperty()`
* [#5237](https://github.com/sebastianbergmann/phpunit/issues/5237): Implement `IgnoreClassForCodeCoverage`, `IgnoreMethodForCodeCoverage`, and `IgnoreFunctionForCodeCoverage` attributes
* [#5293](https://github.com/sebastianbergmann/phpunit/issues/5293): Allow to restrict the reporting of deprecations, notices, and warnings to specified directories
* [#5294](https://github.com/sebastianbergmann/phpunit/issues/5294): Introduce `<source>` XML configuration element to configure "your code"
* [#5300](https://github.com/sebastianbergmann/phpunit/issues/5300): `TestCase::transformException()` hook method
* `TestCase::createConfiguredStub()` was added as an analogon to `TestCase::createConfiguredMock()`
* The `PHPUnit\Event\TestRunner\ExecutionAborted` event is now emitted when the execution of tests is stopped due to `stopOn*` attributes on the `<phpunit>` XML configuration element or due to `--stop-on-*` CLI options
* Event telemetry now includes status information for PHP's garbage collector

### Changed

* [#5198](https://github.com/sebastianbergmann/phpunit/issues/5198): Display PHPUnit deprecations when TestDox output is used
* [#5326](https://github.com/sebastianbergmann/phpunit/pull/5326): Ignore suppressed `E_USER_*` errors again

### Deprecated

* [#5236](https://github.com/sebastianbergmann/phpunit/issues/5236): Deprecate the `CodeCoverageIgnore` attribute
* [#5240](https://github.com/sebastianbergmann/phpunit/issues/5240): Deprecate `TestCase::createTestProxy()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5241](https://github.com/sebastianbergmann/phpunit/issues/5241): Deprecate `TestCase::getMockForAbstractClass()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5242](https://github.com/sebastianbergmann/phpunit/issues/5242): Deprecate `TestCase::getMockFromWsdl()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5243](https://github.com/sebastianbergmann/phpunit/issues/5243): Deprecate `TestCase::getMockForTrait()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5244](https://github.com/sebastianbergmann/phpunit/issues/5244): Deprecate `TestCase::getObjectForTrait()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5305](https://github.com/sebastianbergmann/phpunit/issues/5305): Deprecate `MockBuilder::getMockForAbstractClass()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5306](https://github.com/sebastianbergmann/phpunit/issues/5306): Deprecate `MockBuilder::getMockForTrait()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5307](https://github.com/sebastianbergmann/phpunit/issues/5307): Deprecate `MockBuilder::enableProxyingToOriginalMethods()`, `MockBuilder::disableProxyingToOriginalMethods()`, and `MockBuilder::setProxyTarget()` (these methods only have a `@deprecated` annotation for now; using these methods will trigger a deprecation warning in PHPUnit 11; these methods will be removed in PHPUnit 12)
* [#5308](https://github.com/sebastianbergmann/phpunit/issues/5308): Deprecate `MockBuilder::allowMockingUnknownTypes()` and `MockBuilder::disallowMockingUnknownTypes()` (these methods only have a `@deprecated` annotation for now; using these methods will trigger a deprecation warning in PHPUnit 11; these methods will be removed in PHPUnit 12)
* [#5315](https://github.com/sebastianbergmann/phpunit/issues/5315): Deprecate `MockBuilder::enableArgumentCloning()` and `MockBuilder::disableArgumentCloning()` (these methods only have a `@deprecated` annotation for now; using these methods will trigger a deprecation warning in PHPUnit 11; these methods will be removed in PHPUnit 12)
* [#5320](https://github.com/sebastianbergmann/phpunit/issues/5320): Deprecate `MockBuilder::addMethods()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* Using `<coverage><include>...</include><exclude>...</exclude></coverage>` in the XML configuration file to configure code that should be included in code coverage reporting is now deprecated and support for this will be removed in PHPUnit 11 (use `<source><include>...</include><exclude>...</exclude></source>` instead)
* `PHPUnit\TextUI\Configuration\Configuration::hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport()` (use `source()->notEmpty()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::coverageIncludeDirectories()` (use `source()->includeDirectories()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::coverageIncludeFiles()` (use `source()->includeFiles()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::coverageExcludeDirectories()` (use `source()->excludeDirectories()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::coverageExcludeFiles()` (use `source()->excludeFiles()` instead)

[10.1.3]: https://github.com/sebastianbergmann/phpunit/compare/10.1.2...10.1.3
[10.1.2]: https://github.com/sebastianbergmann/phpunit/compare/10.1.1...10.1.2
[10.1.1]: https://github.com/sebastianbergmann/phpunit/compare/10.1.0...10.1.1
[10.1.0]: https://github.com/sebastianbergmann/phpunit/compare/10.0.19...10.1.0
