# Changes in PHPUnit 12.1

All notable changes of the PHPUnit 12.1 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.1.0] - 2025-04-04

### Added

* [#6118](https://github.com/sebastianbergmann/phpunit/pull/6118): `expectErrorLog()` for expecting `error_log()` output
* [#6126](https://github.com/sebastianbergmann/phpunit/pull/6126): Attribute `#[WithEnvironmentVariable]` for setting an environment variable for the duration of a test
* The `AfterTestMethodCalled`, `AfterTestMethodErrored`, `AfterTestMethodFinished`, `BeforeTestMethodCalled`, `BeforeTestMethodErrored`, `BeforeTestMethodFinished`, `PostConditionCalled`, `PostConditionErrored`, `PostConditionFinished`, `PreConditionCalled`, `PreConditionErrored`, and `PreConditionFinished` event value objects now have `test()` method that returns a value object representing the test method for which the hook method was called

### Deprecated

* [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140): The `testClassName()` method on the `AfterTestMethodCalled`, `AfterTestMethodErrored`, `AfterTestMethodFinished`, `BeforeTestMethodCalled`, `BeforeTestMethodErrored`, `BeforeTestMethodFinished`, `PostConditionCalled`, `PostConditionErrored`, `PostConditionFinished`, `PreConditionCalled`, `PreConditionErrored`, and `PreConditionFinished` event value objects (use `test()->className()` instead)

[12.1.0]: https://github.com/sebastianbergmann/phpunit/compare/12.0...main
