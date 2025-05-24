# Changes in PHPUnit 12.2

All notable changes of the PHPUnit 12.2 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.2.0] - 2025-06-06

### Added

* `--coverage-openclover` CLI option and `<openclover>` XML configuration element to configure reporting of code coverage information in OpenClover XML format; unlike the existing Clover XML reporting that is controlled through the `--coverage-clover` CLI option and `<clover>` XML configuration element, which remains unchanged, the XML documents generated using these new options validate against the OpenClover project's XML schema definition, with one exception: we do not generate the `<testproject>` element. This feature is experimental and the generated XML might change in order to improve compliance with the OpenClover project's XML schema definition further. Such changes will be made in bugfix and/or minor releases even if they break backward compatibility.
* `--with-telemetry` CLI option that can be used together with `--debug` to print debugging information that includes telemetry information
* The `TestCase::provideAdditionalInformation()` method can now be used to emit a `Test\AdditionalInformationProvided` event
* The new `Test\AfterLastTestMethodFailed`, `Test\AfterTestMethodFailed`, `Test\BeforeFirstTestMethodFailed`, `Test\BeforeTestMethodFailed`, `Test\PostConditionFailed`, `Test\PreConditionFailed` events are now emitted instead of `Test\AfterLastTestMethodErrored`, `Test\AfterTestMethodErrored`, `Test\BeforeFirstTestMethodErrored`, `Test\BeforeTestMethodErrored`, `Test\PostConditionErrored`, `Test\PreConditionErrored` when the `Throwable` extends `AssertionFailedError` to distinguish between errors and failures triggered in hook methods
* The new `Test\PreparationErrored` event is now emitted instead of `Test\PreparationFailed` when the `Throwable` does not extend `AssertionFailedError` to distinguish between errors and failures triggered during test preparation
* `Test\PreparationFailed::throwable()`

### Changed

* A warning is now emitted when both `#[CoversNothing]` and `#[Covers*]` (or `#[Uses*]`) are used on a test class
* A warning is now emitted when a hook method such as `setUp()`, for example has a `#[Test]` attribute
* A warning is now emitted when more than one of `#[Small]`, `#[Medium]`, or `#[Large]` is used on a test class
* A warning is now emitted when a data provider provides data sets that have more values than the test method consumes using arguments

[12.2.0]: https://github.com/sebastianbergmann/phpunit/compare/12.1...main
