# Changes in PHPUnit 5.5

All notable changes of the PHPUnit 5.5 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [5.5.0] - 2016-08-05

### Added

* Added the `PHPUnit\Framework\TestCase::createPartialMock()` method for creating partial test doubles using best practice defaults
* Merged [#2203](https://github.com/sebastianbergmann/phpunit/pull/2203):  Ability to `--list-suites` for a given configuration

### Changed

* An `AssertionError` raised by an `assert()` in the tested code now causes the test to be interpreted as a failure instead of an error

[5.5.0]: https://github.com/sebastianbergmann/phpunit/compare/5.4...5.5.0

