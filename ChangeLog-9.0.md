# Changes in PHPUnit 9.0

All notable changes of the PHPUnit 9.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.0.0] - 2020-02-07

### Added

* Implemented [#3797](https://github.com/sebastianbergmann/phpunit/pull/3797): Add support for multiple `--whitelist` options

### Changed

* Implemented [#3630](https://github.com/sebastianbergmann/phpunit/issues/3630): Deprecate support for `ClassName<*>` as values for `@covers` and `@uses` annotations (this deprecation is not implemented in code, you will not get a deprecation warning when you use this feature in PHPUnit 9)
* Implemented [#3770](https://github.com/sebastianbergmann/phpunit/issues/3770): Deprecate `MockBuilder::setMethods()` (this deprecation is not implemented in code, you will not get a deprecation warning when you use this feature in PHPUnit 9)
* Implemented [#3746](https://github.com/sebastianbergmann/phpunit/issues/3746): Improve developer experience of global wrapper functions for assertions
* Implemented [#3914](https://github.com/sebastianbergmann/phpunit/pull/3914): Refactor `PHPUnit\Util\Configuration`
* Implemented [#4024](https://github.com/sebastianbergmann/phpunit/issues/4024): Make `PHPUnit\TextUI\ResultPrinter` an interface
* Multiple test case classes (classes that extend `TestCase`) that are declared in a single sourcecode file are no longer supported

### Removed

* Implemented [#3333](https://github.com/sebastianbergmann/phpunit/issues/3333): Remove annotation(s) for expecting exceptions
* Implemented [#3334](https://github.com/sebastianbergmann/phpunit/issues/3334): Drop support for PHP 7.2
* Implemented [#3339](https://github.com/sebastianbergmann/phpunit/issues/3339): Remove assertions (and helper methods) that operate on (non-public) attributes
* Implemented [#3342](https://github.com/sebastianbergmann/phpunit/issues/3342): Remove optional parameters of `assertEquals()` and `assertNotEquals()`
* Implemented [#3370](https://github.com/sebastianbergmann/phpunit/issues/3370): Remove `assertInternalType()` and `assertNotInternalType()`
* Implemented [#3426](https://github.com/sebastianbergmann/phpunit/issues/3426): Clean up `assertContains()` and `assertNotContains()`
* Implemented [#3495](https://github.com/sebastianbergmann/phpunit/issues/3495): Remove `assertArraySubset()`
* Implemented [#3523](https://github.com/sebastianbergmann/phpunit/issues/3523): Remove the `setUseErrorHandler()` method
* Implemented [#3776](https://github.com/sebastianbergmann/phpunit/issues/3776): Deprecate `expectException(PHPUnit\Framework\Error\*)`
* Implemented [#3951](https://github.com/sebastianbergmann/phpunit/issues/3951): Remove optional parameters of `assertFileEquals()` etc.
* Implemented [#3956](https://github.com/sebastianbergmann/phpunit/issues/3956): Remove support for doubling multiple interfaces
* Implemented [#3957](https://github.com/sebastianbergmann/phpunit/issues/3957): Remove `expectExceptionMessageRegExp()`
* Implemented [#4012](https://github.com/sebastianbergmann/phpunit/issues/4012): Remove class name as CLI argument

[9.0.0]: https://github.com/sebastianbergmann/phpunit/compare/8.5.2...master

