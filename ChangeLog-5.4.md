# Changes in PHPUnit 5.4

All notable changes of the PHPUnit 5.4 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [5.4.0] - 2016-06-03

### Added

* Implemented [#2037](https://github.com/sebastianbergmann/phpunit/issues/2037): Log more information about failures in JSON output

### Changed

* The `PHPUnit_Framework_TestCase::getMock()` method has been deprecated. Please use `PHPUnit_Framework_TestCase::createMock()` or `PHPUnit_Framework_TestCase::getMockBuilder()` instead.
* The `PHPUnit_Framework_TestCase::getMockWithoutInvokingTheOriginalConstructor()` method has been deprecated. Please use `PHPUnit_Framework_TestCase::createMock()` instead.
* The usage of test doubles created via data providers has been improved

[5.4.0]: https://github.com/sebastianbergmann/phpunit/compare/5.3...5.4.0

