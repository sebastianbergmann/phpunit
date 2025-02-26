# Changes in PHPUnit 12.1

All notable changes of the PHPUnit 12.1 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.1.0] - 2025-04-04

### Added

* The `AfterTestMethodCalled`, `AfterTestMethodErrored`, `AfterTestMethodFinished`, `BeforeTestMethodCalled`, `BeforeTestMethodErrored`, `BeforeTestMethodFinished`, `PostConditionCalled`, `PostConditionErrored`, `PostConditionFinished`, `PreConditionCalled`, `PreConditionErrored`, and `PreConditionFinished` event value objects now have `test()` method that returns a value object representing the test method for which the hook method was called

[12.1.0]: https://github.com/sebastianbergmann/phpunit/compare/12.0...main
