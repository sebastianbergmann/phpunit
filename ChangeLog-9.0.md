# Changes in PHPUnit 9.0

All notable changes of the PHPUnit 9.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.0.2] - 2020-03-31

### Fixed

* [#4139](https://github.com/sebastianbergmann/phpunit/issues/4139): Cannot double interfaces that declare a constructor with PHP 8
* [#4144](https://github.com/sebastianbergmann/phpunit/issues/4144): Empty objects are converted to empty arrays in JSON comparison failure diff

## [9.0.1] - 2020-02-13

### Fixed

* [#4036](https://github.com/sebastianbergmann/phpunit/issues/4036): Annotations for ignoring code from code coverage are unintentionally ignored by default

## [9.0.0] - 2020-02-07

### Added

* [#3797](https://github.com/sebastianbergmann/phpunit/pull/3797): Add support for multiple `--whitelist` options

### Changed

* [#3746](https://github.com/sebastianbergmann/phpunit/issues/3746): Improve developer experience of global wrapper functions for assertions
* [#3914](https://github.com/sebastianbergmann/phpunit/pull/3914): Refactor `PHPUnit\Util\Configuration`
* [#4024](https://github.com/sebastianbergmann/phpunit/issues/4024): Make `PHPUnit\TextUI\ResultPrinter` an interface
* Multiple test case classes (classes that extend `TestCase`) that are declared in a single sourcecode file are no longer supported

### Removed

* [#3333](https://github.com/sebastianbergmann/phpunit/issues/3333): Remove annotation(s) for expecting exceptions
* [#3334](https://github.com/sebastianbergmann/phpunit/issues/3334): Drop support for PHP 7.2
* [#3339](https://github.com/sebastianbergmann/phpunit/issues/3339): Remove assertions (and helper methods) that operate on (non-public) attributes
* [#3342](https://github.com/sebastianbergmann/phpunit/issues/3342): Remove optional parameters of `assertEquals()` and `assertNotEquals()`
* [#3370](https://github.com/sebastianbergmann/phpunit/issues/3370): Remove `assertInternalType()` and `assertNotInternalType()`
* [#3426](https://github.com/sebastianbergmann/phpunit/issues/3426): Clean up `assertContains()` and `assertNotContains()`
* [#3495](https://github.com/sebastianbergmann/phpunit/issues/3495): Remove `assertArraySubset()`
* [#3523](https://github.com/sebastianbergmann/phpunit/issues/3523): Remove the `setUseErrorHandler()` method
* [#3630](https://github.com/sebastianbergmann/phpunit/issues/3630): Deprecate support for `ClassName<*>` as values for `@covers` and `@uses` annotations (this deprecation is not implemented in code, you will not get a deprecation warning when you use this feature in PHPUnit 9)
* [#3770](https://github.com/sebastianbergmann/phpunit/issues/3770): Deprecate `MockBuilder::setMethods()` (this deprecation is not implemented in code, you will not get a deprecation warning when you use this feature in PHPUnit 9)
* [#3776](https://github.com/sebastianbergmann/phpunit/issues/3776): Deprecate `expectException(PHPUnit\Framework\Error\*)`
* [#3951](https://github.com/sebastianbergmann/phpunit/issues/3951): Remove optional parameters of `assertFileEquals()` etc.
* [#3956](https://github.com/sebastianbergmann/phpunit/issues/3956): Remove support for doubling multiple interfaces
* [#3957](https://github.com/sebastianbergmann/phpunit/issues/3957): Remove `expectExceptionMessageRegExp()`
* [#4012](https://github.com/sebastianbergmann/phpunit/issues/4012): Remove class name as CLI argument

[9.0.2]: https://github.com/sebastianbergmann/phpunit/compare/9.0.1...9.0.2
[9.0.1]: https://github.com/sebastianbergmann/phpunit/compare/9.0.0...9.0.1
[9.0.0]: https://github.com/sebastianbergmann/phpunit/compare/8.5.2...9.0.0

