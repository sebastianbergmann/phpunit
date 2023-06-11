# Changes in PHPUnit 9.6

All notable changes of the PHPUnit 9.6 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.6.9] - 2023-06-11

### Fixed

* [#5405](https://github.com/sebastianbergmann/phpunit/issues/5405): XML configuration migration does not migrate `whitelist/file` elements
* Always use `X.Y.Z` version number (and not just `X.Y`) of PHPUnit's version when checking whether a PHAR-distributed extension is compatible

## [9.6.8] - 2023-05-11

### Fixed

* [#5345](https://github.com/sebastianbergmann/phpunit/issues/5345): No stack trace shown for previous exceptions during bootstrap

## [9.6.7] - 2023-04-14

### Fixed

* Tests that have `@doesNotPerformAssertions` do not contribute to code coverage

## [9.6.6] - 2023-03-27

### Fixed

* [#5270](https://github.com/sebastianbergmann/phpunit/issues/5270): `GlobalState::getIniSettingsAsString()` generates code that triggers warnings

## [9.6.5] - 2023-03-09

### Changed

* Backported the HTML and CSS improvements made to the `--testdox-html` from PHPUnit 10

### Fixed

* [#5205](https://github.com/sebastianbergmann/phpunit/issues/5205): Wrong default value for optional parameter of `PHPUnit\Util\Test::parseTestMethodAnnotations()` causes `ReflectionException`

## [9.6.4] - 2023-02-27

### Fixed

* [#5186](https://github.com/sebastianbergmann/phpunit/issues/5186): SBOM does not validate

## [9.6.3] - 2023-02-04

### Fixed

* [#5164](https://github.com/sebastianbergmann/phpunit/issues/5164): `markTestSkipped()` not handled correctly when called in "before first test" method

## [9.6.2] - 2023-02-04

### Fixed

* [#4618](https://github.com/sebastianbergmann/phpunit/issues/4618): Support for generators in `assertCount()` etc. is not marked as deprecated in PHPUnit 9.6

## [9.6.1] - 2023-02-03

### Fixed

* [#5073](https://github.com/sebastianbergmann/phpunit/issues/5073): `--no-extensions` CLI option only prevents extension PHARs from being loaded
* [#5160](https://github.com/sebastianbergmann/phpunit/issues/5160): Deprecate `assertClassHasAttribute()`, `assertClassNotHasAttribute()`, `assertClassHasStaticAttribute()`, `assertClassNotHasStaticAttribute()`, `assertObjectHasAttribute()`, `assertObjectNotHasAttribute()`, `classHasAttribute()`, `classHasStaticAttribute()`, and `objectHasAttribute()`

## [9.6.0] - 2023-02-03

### Changed

* [#5062](https://github.com/sebastianbergmann/phpunit/issues/5062): Deprecate `expectDeprecation()`, `expectDeprecationMessage()`, `expectDeprecationMessageMatches()`, `expectError()`, `expectErrorMessage()`, `expectErrorMessageMatches()`, `expectNotice()`, `expectNoticeMessage()`, `expectNoticeMessageMatches()`, `expectWarning()`, `expectWarningMessage()`, and `expectWarningMessageMatches()`
* [#5063](https://github.com/sebastianbergmann/phpunit/issues/5063): Deprecate `withConsecutive()`
* [#5064](https://github.com/sebastianbergmann/phpunit/issues/5064): Deprecate `PHPUnit\Framework\TestCase::getMockClass()`
* [#5132](https://github.com/sebastianbergmann/phpunit/issues/5132): Deprecate `Test` suffix for abstract test case classes

[9.6.9]: https://github.com/sebastianbergmann/phpunit/compare/9.6.8...9.6.9
[9.6.8]: https://github.com/sebastianbergmann/phpunit/compare/9.6.7...9.6.8
[9.6.7]: https://github.com/sebastianbergmann/phpunit/compare/9.6.6...9.6.7
[9.6.6]: https://github.com/sebastianbergmann/phpunit/compare/9.6.5...9.6.6
[9.6.5]: https://github.com/sebastianbergmann/phpunit/compare/9.6.4...9.6.5
[9.6.4]: https://github.com/sebastianbergmann/phpunit/compare/9.6.3...9.6.4
[9.6.3]: https://github.com/sebastianbergmann/phpunit/compare/9.6.2...9.6.3
[9.6.2]: https://github.com/sebastianbergmann/phpunit/compare/9.6.1...9.6.2
[9.6.1]: https://github.com/sebastianbergmann/phpunit/compare/9.6.0...9.6.1
[9.6.0]: https://github.com/sebastianbergmann/phpunit/compare/9.5.28...9.6.0
