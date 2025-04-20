# Changes in PHPUnit 12.2

All notable changes of the PHPUnit 12.2 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.2.0] - 2025-06-06

### Added

* `--with-telemetry` CLI option that can be used together with `--debug` to print debugging information that includes telemetry information
* The new `Test\AfterLastTestMethodFailed`, `Test\AfterTestMethodFailed`, `Test\BeforeFirstTestMethodFailed`, `Test\BeforeTestMethodFailed`, `Test\PostConditionFailed`, `Test\PreConditionFailed` events are now emitted instead of `Test\AfterLastTestMethodErrored`, `Test\AfterTestMethodErrored`, `Test\BeforeFirstTestMethodErrored`, `Test\BeforeTestMethodErrored`, `Test\PostConditionErrored`, `Test\PreConditionErrored` when the `Throwable` extends `AssertionFailedError` to distinguish between errors and failures triggered in hook methods
* The new `Test\PreparationErrored` event is now emitted instead of `Test\PreparationFailed` when the `Throwable` does not extend `AssertionFailedError` to distinguish between errors and failures triggered during test preparation
* `Test\PreparationFailed::throwable()`

[12.2.0]: https://github.com/sebastianbergmann/phpunit/compare/12.1...main
