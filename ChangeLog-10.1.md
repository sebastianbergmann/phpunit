# Changes in PHPUnit 10.1

All notable changes of the PHPUnit 10.1 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [10.1.0] - 2023-04-14

### Added

* [#5168](https://github.com/sebastianbergmann/phpunit/issues/5168): Allow test runner extensions to disable default progress and result printing
* [#5196](https://github.com/sebastianbergmann/phpunit/issues/5196): Optionally (fail|stop) on (notice|deprecation) events
* [#5201](https://github.com/sebastianbergmann/phpunit/issues/5201): Add `TestCase::registerFailureType()` to register interface that marks exceptions to be treated as failures, not errors
* [#5231](https://github.com/sebastianbergmann/phpunit/pull/5231): `assertObjectHasProperty()` and `assertObjectNotHasProperty()`
* [#5237](https://github.com/sebastianbergmann/phpunit/issues/5237): Implement `IgnoreClassForCodeCoverage`, `IgnoreMethodForCodeCoverage`, and `IgnoreFunctionForCodeCoverage` attributes
* [#5293](https://github.com/sebastianbergmann/phpunit/issues/5293): Allow to limit the reporting of deprecations, notices, and warnings to specified directories
* [#5294](https://github.com/sebastianbergmann/phpunit/issues/5294): Introduce `<source>` XML configuration element to configure "your code"
* `TestCase::createConfiguredStub()` was added as an analogon to `TestCase::createConfiguredMock()`
* The `PHPUnit\Event\TestRunner\ExecutionAborted` event is now emitted when the execution of tests is stopped due to `stopOn*` attributes on the `<phpunit>` XML configuration element or due to `--stop-on-*` CLI options

### Changed

* [#5198](https://github.com/sebastianbergmann/phpunit/issues/5198): Display PHPUnit deprecations when TestDox output is used

### Deprecated

* [#5236](https://github.com/sebastianbergmann/phpunit/issues/5236): Deprecate the `CodeCoverageIgnore` attribute
* [#5239](https://github.com/sebastianbergmann/phpunit/issues/5239): Deprecate `TestCase::createPartialMock()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5240](https://github.com/sebastianbergmann/phpunit/issues/5240): Deprecate `TestCase::createTestProxy()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5241](https://github.com/sebastianbergmann/phpunit/issues/5241): Deprecate `TestCase::getMockForAbstractClass()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5242](https://github.com/sebastianbergmann/phpunit/issues/5242): Deprecate `TestCase::getMockFromWsdl()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5243](https://github.com/sebastianbergmann/phpunit/issues/5243): Deprecate `TestCase::getMockForTrait()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5244](https://github.com/sebastianbergmann/phpunit/issues/5244): Deprecate `TestCase::getObjectForTrait()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5252](https://github.com/sebastianbergmann/phpunit/issues/5252): Deprecate `TestCase::getMockBuilder()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* Using `<coverage><include>...</include><exclude>...</exclude></coverage>` in the XML configuration file to configure code that should be included in code coverage reporting is now deprecated and support for this will be removed in PHPUnit 11 (use `<source><include>...</include><exclude>...</exclude></source>` instead)
* `PHPUnit\TextUI\Configuration\Configuration::hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport()` (use `hasNonEmptyListOfSourceFiles()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::coverageIncludeDirectories()` (use `sourceIncludeDirectories()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::coverageIncludeFiles()` (use `sourceIncludeFiles()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::coverageExcludeDirectories()` (use `sourceExcludeDirectories()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::coverageExcludeFiles()` (use `sourceExcludeFiles()` instead)

[10.1.0]: https://github.com/sebastianbergmann/phpunit/compare/10.0...main
