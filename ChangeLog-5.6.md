# Changes in PHPUnit 5.6

All notable changes of the PHPUnit 5.6 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [5.6.0] - 2016-10-07

### Added

* Added `PHPUnit\Framework\TestCase::createConfiguredMock()` based on [idea](https://twitter.com/kriswallsmith/status/763550169090625536) by Kris Wallsmith
* Added the `@doesNotPerformAssertions` annotation for excluding a test from the "useless test" risky test check

### Changed

* Deprecated `PHPUnit\Framework\TestCase::setExpectedExceptionRegExp()`
* `PHPUnit_Util_Printer` no longer optionally cleans up HTML output using `ext/tidy`

[5.6.0]: https://github.com/sebastianbergmann/phpunit/compare/5.5...5.6.0

