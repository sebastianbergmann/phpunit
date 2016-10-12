# Changes in PHPUnit 5.6

All notable changes of the PHPUnit 5.6 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [5.6.1] - 2016-10-07

### Fixed

* Fixed [#2320](https://github.com/sebastianbergmann/phpunit/issues/2320): Conflict between `PHPUnit_Framework_TestCase::getDataSet()` and `PHPUnit_Extensions_Database_TestCase::getDataSet()`

## [5.6.0] - 2016-10-07

### Added

* Merged [#2240](https://github.com/sebastianbergmann/phpunit/pull/2240): Provide access to a test case's data set (for use in `setUp()`, for instance)
* Merged [#2262](https://github.com/sebastianbergmann/phpunit/pull/2262): Add the `PHPUnit_Framework_Constraint_DirectoryExists`, `PHPUnit_Framework_Constraint_IsReadable`, and `PHPUnit_Framework_Constraint_IsWritable` constraints as well as the `assertIsReadable()`, `assertNotIsReadable()`, `assertIsWritable()`, `assertNotIsWritable()`, `assertDirectoryExists()`, `assertDirectoryNotExists()`, `assertDirectoryIsReadable()`, `assertDirectoryNotIsReadable()`, `assertDirectoryIsWritable()`, `assertDirectoryNotIsWritable()`, `assertFileIsReadable()`, `assertFileNotIsReadable()`, `assertFileIsWritable()`, and `assertFileNotIsWritable()` assertions
* Added `PHPUnit\Framework\TestCase::createConfiguredMock()` based on [idea](https://twitter.com/kriswallsmith/status/763550169090625536) by Kris Wallsmith
* Added the `@doesNotPerformAssertions` annotation for excluding a test from the "useless test" risky test check

### Changed

* Deprecated `PHPUnit\Framework\TestCase::setExpectedExceptionRegExp()`
* `PHPUnit_Util_Printer` no longer optionally cleans up HTML output using `ext/tidy`

[5.6.1]: https://github.com/sebastianbergmann/phpunit/compare/5.6.0...5.6.1
[5.6.0]: https://github.com/sebastianbergmann/phpunit/compare/5.5...5.6.0

