# Changes in PHPUnit 10.5

All notable changes of the PHPUnit 10.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [10.5.10] - 2024-02-04

### Fixed

* [#5692](https://github.com/sebastianbergmann/phpunit/issues/5692): `--log-events-text` and `--log-events-verbose-text` require the destination file to exit

## [10.5.9] - 2024-01-22

### Fixed

* [#5676](https://github.com/sebastianbergmann/phpunit/issues/5676): PHPUnit's test runner overwrites custom error handler registered using `set_error_handler()` in bootstrap script

## [10.5.8] - 2024-01-19

### Fixed

* [#5673](https://github.com/sebastianbergmann/phpunit/issues/5673): Confusing error message when migration of a configuration is requested that does not need to be migrated

## [10.5.7] - 2024-01-14

### Fixed

* [#5662](https://github.com/sebastianbergmann/phpunit/issues/5662): PHPUnit errors out on startup when the `ctype` extension is not loaded but a polyfill for it was installed

## [10.5.6] - 2024-01-13

### Added

* Added the `--debug` CLI option as an alias for `--no-output --log-events-text php://stdout`

### Fixed

* [#5455](https://github.com/sebastianbergmann/phpunit/issues/5455): `willReturnCallback()` does not pass unknown named variadic arguments to callback
* [#5488](https://github.com/sebastianbergmann/phpunit/issues/5488): Details about tests that are considered risky are not displayed when the TestDox result printer is used
* [#5516](https://github.com/sebastianbergmann/phpunit/issues/5516): Assertions that use the `LogicalNot` constraint (`assertNotEquals()`, `assertStringNotContainsString()`, ...) can generate confusing failure messages
* [#5518](https://github.com/sebastianbergmann/phpunit/issues/5518): Details about deprecations, notices, and warnings are not displayed when the TestDox result printer is used
* [#5574](https://github.com/sebastianbergmann/phpunit/issues/5574): Wrong backtrace line is reported
* [#5633](https://github.com/sebastianbergmann/phpunit/pull/5633): `--log-events-text` and `--log-events-verbose-text` CLI options do not handle absolute and relative paths
* [#5634](https://github.com/sebastianbergmann/phpunit/pull/5634): Exceptions in the destructor of a test double are ignored
* [#5641](https://github.com/sebastianbergmann/phpunit/issues/5641): The `TestSuite` value object returned by `TestSuite\Filtered::testSuite()` contains all tests instead of only the filtered tests

## [10.5.5] - 2023-12-27

### Fixed

* [#5619](https://github.com/sebastianbergmann/phpunit/pull/5619): Reverted change introduced in PHPUnit 10.5.4 that broke backward compatibility

## [10.5.4] - 2023-12-27

### Fixed

* [#5592](https://github.com/sebastianbergmann/phpunit/issues/5592): Error Handler prevents `error_get_last()` usage in tests
* [#5592](https://github.com/sebastianbergmann/phpunit/issues/5592): `E_USER_ERROR` does not abort test execution
* [#5612](https://github.com/sebastianbergmann/phpunit/issues/5612): Empty `<coverage>` element in XML configuration after migrating configuration
* [#5616](https://github.com/sebastianbergmann/phpunit/issues/5616): Values from data provider are not shown for failed test
* [#5619](https://github.com/sebastianbergmann/phpunit/pull/5619): Check and restore error/exception global handlers
* [#5621](https://github.com/sebastianbergmann/phpunit/issues/5621): Name of data set is missing from TeamCity output

## [10.5.3] - 2023-12-13

### Deprecated

* `Test\AssertionFailed` and `Test\AssertionSucceeded` events
* `PHPUnit\Runner\Extension\Facade::requireExportOfObjects()` and `PHPUnit\Runner\Extension\Facade::requiresExportOfObjects()`
* `registerMockObjectsFromTestArgumentsRecursively` attribute on the `<phpunit>` element of the XML configuration file
* `PHPUnit\TextUI\Configuration\Configuration::registerMockObjectsFromTestArgumentsRecursively()`

### Fixed

* [#5614](https://github.com/sebastianbergmann/phpunit/issues/5614): Infinite recursion when data provider provides recursive array

## [10.5.2] - 2023-12-05

### Fixed

* [#5561](https://github.com/sebastianbergmann/phpunit/issues/5561): JUnit XML logger does not handle assertion failures in before-test methods
* [#5567](https://github.com/sebastianbergmann/phpunit/issues/5567): Infinite recursion when recursive / self-referencing arrays are checked whether they contain only scalar values

## [10.5.1] - 2023-12-01

### Fixed

* [#5593](https://github.com/sebastianbergmann/phpunit/issues/5593): Return Value Generator fails to correctly create test stub for method with `static` return type declaration when used recursively
* [#5596](https://github.com/sebastianbergmann/phpunit/issues/5596): `PHPUnit\Framework\TestCase` has `@internal` annotation in PHAR

## [10.5.0] - 2023-12-01

### Added

* [#5532](https://github.com/sebastianbergmann/phpunit/issues/5532): `#[IgnoreDeprecations]` attribute to ignore `E_(USER_)DEPRECATED` issues on test class and test method level
* [#5551](https://github.com/sebastianbergmann/phpunit/issues/5551): Support for omitting parameter default values for `willReturnMap()` 
* [#5577](https://github.com/sebastianbergmann/phpunit/issues/5577): `--composer-lock` CLI option for PHAR binary that displays the `composer.lock` used to build the PHAR 

### Changed

* `MockBuilder::disableAutoReturnValueGeneration()` and `MockBuilder::enableAutoReturnValueGeneration()` are no longer deprecated

### Fixed

* [#5563](https://github.com/sebastianbergmann/phpunit/issues/5563): `createMockForIntersectionOfInterfaces()` does not automatically register mock object for expectation verification

[10.5.10]: https://github.com/sebastianbergmann/phpunit/compare/10.5.9...10.5.10
[10.5.9]: https://github.com/sebastianbergmann/phpunit/compare/10.5.8...10.5.9
[10.5.8]: https://github.com/sebastianbergmann/phpunit/compare/10.5.7...10.5.8
[10.5.7]: https://github.com/sebastianbergmann/phpunit/compare/10.5.6...10.5.7
[10.5.6]: https://github.com/sebastianbergmann/phpunit/compare/10.5.5...10.5.6
[10.5.5]: https://github.com/sebastianbergmann/phpunit/compare/10.5.4...10.5.5
[10.5.4]: https://github.com/sebastianbergmann/phpunit/compare/10.5.3...10.5.4
[10.5.3]: https://github.com/sebastianbergmann/phpunit/compare/10.5.2...10.5.3
[10.5.2]: https://github.com/sebastianbergmann/phpunit/compare/10.5.1...10.5.2
[10.5.1]: https://github.com/sebastianbergmann/phpunit/compare/10.5.0...10.5.1
[10.5.0]: https://github.com/sebastianbergmann/phpunit/compare/10.4.2...10.5.0
