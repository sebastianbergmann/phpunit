# Changes in PHPUnit 5.1

All notable changes of the PHPUnit 5.1 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [5.1.7] - 2016-02-02

### Fixed

* Fixed [#2050](https://github.com/sebastianbergmann/phpunit/issues/2050): `PHPUnit_Util_XML::load()` raises exception with empty message when XML string is empty

## [5.1.6] - 2016-01-29

### Fixed

* Fixed [#2052](https://github.com/sebastianbergmann/phpunit/issues/2052): PHPUnit 5.1.5 breaks coverage whitelist include directory globbing

## [5.1.5] - 2016-01-29

### Fixed

* An exception is now raised when non-existant directories or files are to be added to the code coverage whitelist
* Fixed a bug in `PHPUnit_Runner_Version::series()`

## [5.1.4] - 2016-01-11

### Fixed

* Fixed [#1959](https://github.com/sebastianbergmann/phpunit/issues/1959): Prophecy errors are not handled correctly

### Fixed

## [5.1.3] - 2015-12-10

### Added

* Added support for `Throwable` to `PHPUnit_Framework_TestCase::throwsException()`

## [5.1.2] - 2015-12-07

### Fixed

* Fixed a backwards compatibility break related to the execution order of `@before` and `setUp()` introduced in PHPUnit 5.1.0

## [5.1.1] - 2015-12-07

### Fixed

* Fixed a backwards compatibility break in the `PHPUnit_Framework_TestListener` interface introduced in PHPUnit 5.1.0

## [5.1.0] - 2015-12-04

### Added

* Implemented [#1802](https://github.com/sebastianbergmann/phpunit/issues/1802): Refactor how PHPUnit emits warnings (such as `No tests found in class "Test"`)
* Merged [#1824](https://github.com/sebastianbergmann/phpunit/issues/1824): Added support for the `--CLEAN--` and `--EXPECTREGEX--` sections for PHPT test cases
* Merged [#1825](https://github.com/sebastianbergmann/phpunit/issues/1825): Redirect STDERR to STDOUT when running PHPT test cases
* Merged [#1871](https://github.com/sebastianbergmann/phpunit/issues/1871): Added support for `@testdox` annotations on classes
* Merged [#1917](https://github.com/sebastianbergmann/phpunit/issues/1917): Allow `@coversDefaultClass` annotation to work on traits

[5.1.7]: https://github.com/sebastianbergmann/phpunit/compare/5.1.6...5.1.7
[5.1.6]: https://github.com/sebastianbergmann/phpunit/compare/5.1.5...5.1.6
[5.1.5]: https://github.com/sebastianbergmann/phpunit/compare/5.1.4...5.1.5
[5.1.4]: https://github.com/sebastianbergmann/phpunit/compare/5.1.3...5.1.4
[5.1.3]: https://github.com/sebastianbergmann/phpunit/compare/5.1.2...5.1.3
[5.1.2]: https://github.com/sebastianbergmann/phpunit/compare/5.1.1...5.1.2
[5.1.1]: https://github.com/sebastianbergmann/phpunit/compare/5.1.0...5.1.1
[5.1.0]: https://github.com/sebastianbergmann/phpunit/compare/5.0...5.1.0

