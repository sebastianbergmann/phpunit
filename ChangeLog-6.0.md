# Changes in PHPUnit 6.0

All notable changes of the PHPUnit 6.0 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [6.0.0] - 2017-02-03

### Changed

* `setUpBeforeClass()` is now invoked after all methods annotated with `@beforeClass`
* `setUp()` is now invoked after all methods annotated with `@before`
* Added `addWarning()` method to `PHPUnit\Framework\TestListener` interface

### Removed

* Removed `PHPUnit\Framework\TestCase::getMock()` (deprecated in PHPUnit 5.4)
* Removed `PHPUnit\Framework\TestCase::getMockWithoutInvokingTheOriginalConstructor()` (deprecated in PHPUnit 5.4)
* Removed `PHPUnit\Framework\TestCase::setExpectedException()` (deprecated in PHPUnit 5.2)
* Removed `PHPUnit\Framework\TestCase::setExpectedExceptionRegExp()` (deprecated in PHPUnit 5.6)
* Removed `PHPUnit\Framework\TestCase::hasPerformedExpectationsOnOutput()` (deprecated in PHPUnit 4.3)
* Removed the `checkForUnintentionallyCoveredCode` configuration setting (deprecated in PHPUnit 5.2)
* PHPUnit is no longer supported on PHP 5.6

[6.0.0]: https://github.com/sebastianbergmann/phpunit/compare/5.7...6.0.0

