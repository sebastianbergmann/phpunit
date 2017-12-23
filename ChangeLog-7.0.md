# Changes in PHPUnit 7.0

All notable changes of the PHPUnit 7.0 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [7.0.0] - 2018-02-02

### Changed

* Implemented [#2566](https://github.com/sebastianbergmann/phpunit/issues/2566): Use `Throwable` instead of `Exception` in `PHPUnit\Framework\TestListener` method signatures
* Implemented [#2920](https://github.com/sebastianbergmann/phpunit/pull/2920): Replace CLI TestDox printer with `rpkamp/fancy-testdox-printer`

### Removed

* Implemented [#2473](https://github.com/sebastianbergmann/phpunit/issues/2473): Drop support for PHP 7.0
* `@scenario` is no longer an alias for `@test`
* The `PHPUnit\Framework\BaseTestListener` class has been removed (deprecated in PHPUnit 6.4)

### Fixed

* Fixed [#2902](https://github.com/sebastianbergmann/phpunit/issues/2902): `@test` annotation gets accepted no matter what
* Fixed [#2907](https://github.com/sebastianbergmann/phpunit/issues/2907): `StringMatchesFormatDescription` constraint does not handle escaped `%` correctly

[7.0.0]: https://github.com/sebastianbergmann/phpunit/compare/6.5...7.0.0

