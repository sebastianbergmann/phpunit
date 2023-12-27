# Changes in PHPUnit 10.5

All notable changes of the PHPUnit 10.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

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

[10.5.5]: https://github.com/sebastianbergmann/phpunit/compare/10.5.4...10.5.5
[10.5.4]: https://github.com/sebastianbergmann/phpunit/compare/10.5.3...10.5.4
[10.5.3]: https://github.com/sebastianbergmann/phpunit/compare/10.5.2...10.5.3
[10.5.2]: https://github.com/sebastianbergmann/phpunit/compare/10.5.1...10.5.2
[10.5.1]: https://github.com/sebastianbergmann/phpunit/compare/10.5.0...10.5.1
[10.5.0]: https://github.com/sebastianbergmann/phpunit/compare/10.4.2...10.5.0
