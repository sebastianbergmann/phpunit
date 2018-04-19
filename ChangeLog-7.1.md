# Changes in PHPUnit 7.1

All notable changes of the PHPUnit 7.1 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [7.1.4] - 2018-04-18

### Fixed

* Fixed [#3034](https://github.com/sebastianbergmann/phpunit/pull/3034): `$this->getStatus()` returns `STATUS_PASSED` in `tearDown()` after unexpected exception

## [7.1.3] - 2018-04-13

### Fixed

* Fixed [#3094](https://github.com/sebastianbergmann/phpunit/issues/3094): Faulty dependency constraint affecting `getObjectForTrait()` (failure using `--prefer-lowest`)

## [7.1.2] - 2018-04-10

### Fixed

* Fixed [#2830](https://github.com/sebastianbergmann/phpunit/issues/2830): `@runClassInSeparateProcess` does not work for tests that use `@dataProvider`
* Fixed [#3059](https://github.com/sebastianbergmann/phpunit/pull/3059): `StringMatchesFormatDescription` constraint fails when matching multiline with `\r\n`
* Fixed [#3087](https://github.com/sebastianbergmann/phpunit/pull/3087): `TestCase::getTestResultObject()` can return `null`

## [7.1.1] - 2018-04-06

### Fixed

* `CliTestDoxPrinter::writeProgress()` and `TeamCity::writeProgress()` are not compatible with `ResultPrinter::writeProgress()` (on PHP 7.1)

## [7.1.0] - 2018-04-06

### Added

* Implemented [#3002](https://github.com/sebastianbergmann/phpunit/issues/3002): Support for test runner extensions
* Implemented [#3035](https://github.com/sebastianbergmann/phpunit/pull/3035): Add support for `iterable` in `assertInternalType()`

### Changed

* `PHPUnit\Framework\Assert` is no longer searched for test methods
* `ReflectionMethod::invokeArgs()` is no longer used to invoke test methods

[7.1.4]: https://github.com/sebastianbergmann/phpunit/compare/7.1.3...7.1.4
[7.1.3]: https://github.com/sebastianbergmann/phpunit/compare/7.1.2...7.1.3
[7.1.2]: https://github.com/sebastianbergmann/phpunit/compare/7.1.1...7.1.2
[7.1.1]: https://github.com/sebastianbergmann/phpunit/compare/7.1.0...7.1.1
[7.1.0]: https://github.com/sebastianbergmann/phpunit/compare/7.0...7.1.0

