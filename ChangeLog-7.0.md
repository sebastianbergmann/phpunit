# Changes in PHPUnit 7.0

All notable changes of the PHPUnit 7.0 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [7.0.1] - 2018-MM-DD

### Fixed

* Fixed [#3000](https://github.com/sebastianbergmann/phpunit/issues/3000): Directories are not created recursively
* Removed superfluous `@throws \Exception` annotations from assertion methods

## [7.0.0] - 2018-02-02

### Added

* Implemented [#2967](https://github.com/sebastianbergmann/phpunit/pull/2967): Added support for PHP configuration settings to `@requires` annotation

### Changed

* Implemented [#2566](https://github.com/sebastianbergmann/phpunit/issues/2566): Use `Throwable` instead of `Exception` in `PHPUnit\Framework\TestListener` method signatures
* Implemented [#2920](https://github.com/sebastianbergmann/phpunit/pull/2920): Replace CLI TestDox printer with `rpkamp/fancy-testdox-printer`
* Scalar Type Declarations and Return Type Declarations are now used where possible (as a result, the API of `PHPUnit\Framework\TestListener`, for instance, has changed)
* Some classes are now `final`
* The visibility of some methods has been changed from `protected` to `private`

### Removed

* Implemented [#2473](https://github.com/sebastianbergmann/phpunit/issues/2473): Drop support for PHP 7.0
* `@scenario` is no longer an alias for `@test`
* The `PHPUnit\Framework\BaseTestListener` class has been removed (deprecated in PHPUnit 6.4)
* The `PHPUnit\Framework\TestCase::prepareTemplate` template method has been removed

### Fixed

* Fixed [#2169](https://github.com/sebastianbergmann/phpunit/issues/2169): `assertSame()` does not show differences when used on two arrays that are not identical
* Fixed [#2902](https://github.com/sebastianbergmann/phpunit/issues/2902): `@test` annotation gets accepted no matter what
* Fixed [#2907](https://github.com/sebastianbergmann/phpunit/issues/2907): `StringMatchesFormatDescription` constraint does not handle escaped `%` correctly
* Fixed [#2919](https://github.com/sebastianbergmann/phpunit/issues/2919): `assertJsonStringEqualsJsonString()` matches empty object as empty array

[7.0.1]: https://github.com/sebastianbergmann/phpunit/compare/7.0.0...7.0.1
[7.0.0]: https://github.com/sebastianbergmann/phpunit/compare/6.5...7.0.0

