# Changes in PHPUnit 8.4

All notable changes of the PHPUnit 8.4 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [8.4.2] - 2019-10-28

### Fixed

* Fixed [#3727](https://github.com/sebastianbergmann/phpunit/issues/3727): Problem hidden by PHPUnit's error handler
* Fixed [#3793](https://github.com/sebastianbergmann/phpunit/issues/3793): JUnit logger reports warnings as failures
* Fixed [#3863](https://github.com/sebastianbergmann/phpunit/pull/3863): `\Countable` needs to be checked before `\EmptyIterator`
* Fixed [#3889](https://github.com/sebastianbergmann/phpunit/issues/3889): Test(s) not found when source filename does not match test case class name
* Fixed [#3893](https://github.com/sebastianbergmann/phpunit/issues/3893): `TypeError` when called with a filename without extension

## [8.4.1] - 2019-10-07

### Fixed

* Fixed [#3879](https://github.com/sebastianbergmann/phpunit/issues/3879): Tests with data providers in parent class do not work anymore
* Fixed [#3881](https://github.com/sebastianbergmann/phpunit/issues/3881): Regression with multiple test case classes declared in a single sourcecode file
* Fixed [#3884](https://github.com/sebastianbergmann/phpunit/issues/3884): Uncaught `ReflectionException` with TestDox XML report

## [8.4.0] - 2019-10-04

### Added

* Implemented [#3120](https://github.com/sebastianbergmann/phpunit/issues/3120): Provide `TestCase::createStub()` method as alternative to `TestCase::createMock()`
* Implemented [#3775](https://github.com/sebastianbergmann/phpunit/issues/3775): Explicit API for expecting PHP errors, warnings, and notices

### Changed

* The method `expectExceptionMessageRegExp()` is now deprecated. There is no behavioral change in this version of PHPUnit. Using this method will trigger a deprecation warning in PHPUnit 9 and in PHPUnit 10 it will be removed. Please use `expectExceptionMessageMatches()` instead.

[8.4.2]: https://github.com/sebastianbergmann/phpunit/compare/8.4.1...8.4.2
[8.4.1]: https://github.com/sebastianbergmann/phpunit/compare/8.4.0...8.4.1
[8.4.0]: https://github.com/sebastianbergmann/phpunit/compare/8.3.5...8.4.0

