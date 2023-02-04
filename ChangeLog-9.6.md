# Changes in PHPUnit 9.6

All notable changes of the PHPUnit 9.6 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.6.3] - 2023-02-04

### Fixed

* [#5164](https://github.com/sebastianbergmann/phpunit/issues/5164): `markTestSkipped()` not handled correctly when called in "before first test" method

## [9.6.2] - 2023-02-04

### Fixed

* [#4618](https://github.com/sebastianbergmann/phpunit/issues/4618): Support for generators in `assertCount()` etc. is not marked as deprecated in PHPUnit 9.6

## [9.6.1] - 2023-02-03

### Fixed

* [#5073](https://github.com/sebastianbergmann/phpunit/issues/5073): `--no-extensions` CLI option only prevents extension PHARs from being loaded
* [#5160](https://github.com/sebastianbergmann/phpunit/issues/5160): PHPUnit 9.6 misses deprecations for assertions and constraints removed in PHPUnit 10

## [9.6.0] - 2023-02-03

### Changed

* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): Deprecate `expectDeprecation()`, `expectDeprecationMessage()`, `expectDeprecationMessageMatches()`, `expectError()`, `expectErrorMessage()`, `expectErrorMessageMatches()`, `expectNotice()`, `expectNoticeMessage()`, `expectNoticeMessageMatches()`, `expectWarning()`, `expectWarningMessage()`, and `expectWarningMessageMatches()`
* [#5063](https://github.com/sebastianbergmann/phpunit/issues/5063): Deprecate `withConsecutive()`
* [#5064](https://github.com/sebastianbergmann/phpunit/issues/5064): Deprecate `PHPUnit\Framework\TestCase::getMockClass()`
* [#5132](https://github.com/sebastianbergmann/phpunit/issues/5132): Deprecate `Test` suffix for abstract test case classes

[9.6.3]: https://github.com/sebastianbergmann/phpunit/compare/9.6.2...9.6.3
[9.6.2]: https://github.com/sebastianbergmann/phpunit/compare/9.6.1...9.6.2
[9.6.1]: https://github.com/sebastianbergmann/phpunit/compare/9.6.0...9.6.1
[9.6.0]: https://github.com/sebastianbergmann/phpunit/compare/9.5.28...9.6.0
