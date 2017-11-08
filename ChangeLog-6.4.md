# Changes in PHPUnit 6.4

All notable changes of the PHPUnit 6.4 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [6.4.4] - 2017-11-08

### Fixed

* Fixed [#2814](https://github.com/sebastianbergmann/phpunit/pull/2814): Incorrect signature of `expectExceptionObject()`
* Fixed [#2844](https://github.com/sebastianbergmann/phpunit/issues/2844): The commandline option `--whitelist` has no effect

## [6.4.3] - 2017-10-16

### Fixed

* Fixed [#2811](https://github.com/sebastianbergmann/phpunit/issues/2811): `expectExceptionMessage()` does not work without `expectException()`

## [6.4.2] - 2017-10-15

### Fixed

* Fixed [#1873](https://github.com/sebastianbergmann/phpunit/issues/1873): Arguments for an object within a listener cannot accept multiple arguments of the same type
* Fixed [#2237](https://github.com/sebastianbergmann/phpunit/issues/2237): `assertArraySubset()` should provide the diff when the assertion fails
* Fixed [#2688](https://github.com/sebastianbergmann/phpunit/issues/2688): `assertNotContains()` interferes with actual string
* Fixed [#2693](https://github.com/sebastianbergmann/phpunit/issues/2693): Second `yield from` is not called from a data provider
* Fixed [#2721](https://github.com/sebastianbergmann/phpunit/issues/2721): Confusing failure message when `assertFileNotEquals()` is used on two empty files
* Fixed [#2731](https://github.com/sebastianbergmann/phpunit/issues/2731): Empty exception message cannot be expected
* Fixed [#2778](https://github.com/sebastianbergmann/phpunit/issues/2778): `assertContains()` does not handle empty strings in strings correctly

## [6.4.1] - 2017-10-07

### Fixed

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

[6.4.4]: https://github.com/sebastianbergmann/phpunit/compare/6.4.3...6.4.4
[6.4.3]: https://github.com/sebastianbergmann/phpunit/compare/6.4.2...6.4.3
[6.4.2]: https://github.com/sebastianbergmann/phpunit/compare/6.4.1...6.4.2
[6.4.1]: https://github.com/sebastianbergmann/phpunit/compare/6.4.0...6.4.1
[6.4.0]: https://github.com/sebastianbergmann/phpunit/compare/6.3...6.4.0

