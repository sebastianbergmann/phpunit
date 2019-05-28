# Changes in PHPUnit 8.1

All notable changes of the PHPUnit 8.1 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [8.1.6] - 2019-05-28

### Changed

* After each test, `libxml_clear_errors()` is now called to clear the libxml error buffer

### Fixed

* Fixed [#3694](https://github.com/sebastianbergmann/phpunit/pull/3694): Constructor arguments for `Throwable` and `Exception` are ignored
* Fixed [#3709](https://github.com/sebastianbergmann/phpunit/pull/3709): Method-level `@coversNothing` annotation does not prevent code coverage data collection

## [8.1.5] - 2019-05-14

### Fixed

* Fixed [#3683](https://github.com/sebastianbergmann/phpunit/issues/3683): Regression in PHPUnit 8.1.4 with regard to Exception stubbing/mocking

## [8.1.4] - 2019-05-09

### Fixed

* Fixed [#3414](https://github.com/sebastianbergmann/phpunit/pull/3414): `willThrowException()` only accepts `Exception`, not `Throwable`
* Fixed [#3559](https://github.com/sebastianbergmann/phpunit/issues/3559): No diff for failed PHPT EXPECT
* Fixed [#3587](https://github.com/sebastianbergmann/phpunit/issues/3587): `.phpunit.result.cache` file is all over the place
* Fixed [#3596](https://github.com/sebastianbergmann/phpunit/issues/3596): Mocking an interface that extends another interface forgets to mock its own methods
* Fixed [#3599](https://github.com/sebastianbergmann/phpunit/issues/3599): Type error in `TestCase::createGlobalStateSnapshot()`
* Fixed [#3614](https://github.com/sebastianbergmann/phpunit/pull/3599): `PHPUnit\Framework\Constraint\Attribute` should be deprecated (and ignored from code coverage)
* Fixed [#3674](https://github.com/sebastianbergmann/phpunit/issues/3674): `TypeError` when an incorrect file path is given

## [8.1.3] - 2019-04-19

### Fixed

* Fixed [#3607](https://github.com/sebastianbergmann/phpunit/issues/3607): Return value generation interferes with proxying to original method

## [8.1.2] - 2019-04-08

### Fixed

* Fixed [#3600](https://github.com/sebastianbergmann/phpunit/pull/3600): Wrong class name in docblock

## [8.1.1] - 2019-04-08

### Fixed

* Fixed [#3588](https://github.com/sebastianbergmann/phpunit/issues/3588): PHPUnit 8.1.0 breaks static analysis of MockObject usage

## [8.1.0] - 2019-04-05

### Added

* Implemented [#3528](https://github.com/sebastianbergmann/phpunit/pull/3528): Option to disable TestDox progress animation
* Implemented [#3556](https://github.com/sebastianbergmann/phpunit/issues/3556): Configure TestDox result printer via configuration file
* Implemented [#3558](https://github.com/sebastianbergmann/phpunit/issues/3558): `TestCase::getDependencyInput()`
* Information on test groups in the TestDox XML report is now reported in `group` elements that are child nodes of `test`
* Information from `@covers` and `@uses` annotations is now reported in TestDox XML
* Information on test doubles used in a test is now reported in TestDox XML

### Changed

* The `groups` attribute on the `test` element in the TestDox XML report is now deprecated

[8.1.6]: https://github.com/sebastianbergmann/phpunit/compare/8.1.5...8.1.6
[8.1.5]: https://github.com/sebastianbergmann/phpunit/compare/8.1.4...8.1.5
[8.1.4]: https://github.com/sebastianbergmann/phpunit/compare/8.1.3...8.1.4
[8.1.3]: https://github.com/sebastianbergmann/phpunit/compare/8.1.2...8.1.3
[8.1.2]: https://github.com/sebastianbergmann/phpunit/compare/8.1.1...8.1.2
[8.1.1]: https://github.com/sebastianbergmann/phpunit/compare/8.1.0...8.1.1
[8.1.0]: https://github.com/sebastianbergmann/phpunit/compare/8.0.6...8.1.0

