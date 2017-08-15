# Changes in PHPUnit 6.4

All notable changes of the PHPUnit 6.4 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [6.4.0] - 2017-10-06

### Added

* Implemented [#1993](https://github.com/sebastianbergmann/phpunit/issues/1993): Add `--list-tests` and `--list-tests-raw` options for listing all available tests (without executing them)
* Added `TestListenerDefaultImplementation` trait that provides empty implementations of the `TestListener` methods

### Changed

* The `PHPUnit\Framework\BaseTestListener` class is now deprecated

### Fixed

* Fixed [#2750](https://github.com/sebastianbergmann/phpunit/issues/2750): Useless call to `array_map()`

[6.4.0]: https://github.com/sebastianbergmann/phpunit/compare/6.3...6.4.0

