# Changes in PHPUnit 6.4

All notable changes of the PHPUnit 6.4 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [6.4.1] - 2017-10-07

* Fixed [#2791](https://github.com/sebastianbergmann/phpunit/issues/2791): Process Isolation does not work with PHPUnit PHAR
* Fixed [#2792](https://github.com/sebastianbergmann/phpunit/issues/2792): `get_resource_type()` expects parameter 1 to be resource, string given

## [6.4.0] - 2017-10-06

### Added

* Implemented [#1993](https://github.com/sebastianbergmann/phpunit/issues/1993): Add `--list-tests` and `--list-tests-xml` options for listing all tests (without executing them)
* Implemented [#2780](https://github.com/sebastianbergmann/phpunit/pull/2780): Add support for expecting exceptions based on `Exception` objects
* Added `TestCase::registerComparator()` to register custom `Comparator` implementations for use with `assertEquals()` that are automatically unregistered after the test
* Added `TestListenerDefaultImplementation` trait that provides empty implementations of the `TestListener` methods

### Changed

* The `PHPUnit\Framework\BaseTestListener` class is now deprecated

### Fixed

* Fixed [#2750](https://github.com/sebastianbergmann/phpunit/issues/2750): Useless call to `array_map()`

[6.4.1]: https://github.com/sebastianbergmann/phpunit/compare/6.4.0...6.4.1
[6.4.0]: https://github.com/sebastianbergmann/phpunit/compare/6.3...6.4.0

