# Changes in PHPUnit 7.0

All notable changes of the PHPUnit 7.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [7.0.3] - 2018-03-26

* Fixed [#3028](https://github.com/sebastianbergmann/phpunit/pull/3028): TestDox name prettifier does not handle test case classes correctly that are in a `Tests\*` namespace

## [7.0.2] - 2018-02-26

### Fixed

* Fixed [#2974](https://github.com/sebastianbergmann/phpunit/issues/2974): JUnit XML logfile contains invalid characters when test output contains binary data
* Fixed [#3014](https://github.com/sebastianbergmann/phpunit/issues/3014): `TypeError` in `PHPUnit\Framework\TestCase::getActualOutput()` when callback registered using `setOutputCallback()` does not return a string
* Removed more superfluous `@throws \Exception` annotations

## [7.0.1] - 2018-02-13

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

[7.0.3]: https://github.com/sebastianbergmann/phpunit/compare/7.0.2...7.0.3
[7.0.2]: https://github.com/sebastianbergmann/phpunit/compare/7.0.1...7.0.2
[7.0.1]: https://github.com/sebastianbergmann/phpunit/compare/7.0.0...7.0.1
[7.0.0]: https://github.com/sebastianbergmann/phpunit/compare/6.5...7.0.0

