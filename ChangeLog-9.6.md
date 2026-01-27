# Changes in PHPUnit 9.6

All notable changes of the PHPUnit 9.6 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.6.34] - 2026-01-27

### Fixed

* Regression introduced in PHPUnit 9.6.33

## [9.6.33] - 2026-01-27

### Changed

* To prevent Poisoned Pipeline Execution (PPE) attacks using prepared `.coverage` files in pull requests, a PHPT test will no longer be run if the temporary file for writing code coverage information already exists before the test runs

## [9.6.32] - 2026-01-24

### Changed

* `PHPUnit\Framework\MockObject` exceptions are now subtypes of `PHPUnit\Exception`

## [9.6.31] - 2025-12-06

* No changes; `phpunit.phar` rebuilt with PHP 8.4 to work around PHP-Scoper issue [#1139](https://github.com/humbug/php-scoper/issues/1139)

## [9.6.30] - 2025-12-01

### Changed

* Updated list of deprecated PHP configuration settings for PHP 8.4, PHP 8.5, and PHP 8.6

## [9.6.29] - 2025-09-24

* No changes; `phpunit.phar` rebuilt with updated dependencies

## [9.6.28] - 2025-09-23

* No changes; `phpunit.phar` rebuilt with updated dependencies

## [9.6.27] - 2025-09-14

### Changed

* [#6366](https://github.com/sebastianbergmann/phpunit/issues/6366): Exclude `__sleep()` and `__wakeup()` from test double code generation on PHP >= 8.5

## [9.6.26] - 2025-09-11

### Changed

* Implement `__serialize()` in addition to `__sleep()` (which will be deprecated in PHP 8.5)

## [9.6.25] - 2025-08-20

### Changed

* Do not configure `report_memleaks` setting (which will be deprecated in PHP 8.5) for PHPT processes

## [9.6.24] - 2025-08-10

### Changed

* Do not use `ReflectionProperty::setAccessible()` with PHP >= 8.1
* Do not use `SplObjectStorage` methods that will be deprecated in PHP 8.5

## [9.6.23] - 2025-05-02

### Changed

* [#5956](https://github.com/sebastianbergmann/phpunit/issues/5956): Improved handling of deprecated `E_STRICT` constant
* Improved message when test is considered risky for printing unexpected output

## [9.6.22] - 2024-12-05

### Fixed

* [#6071](https://github.com/sebastianbergmann/phpunit/issues/6071): PHP Archives (PHARs) of PHPUnit 8.5 and PHPUnit 9.6 bundle outdated versions of Prophecy

## [9.6.21] - 2024-09-19

### Changed

* [#5956](https://github.com/sebastianbergmann/phpunit/issues/5956): Deprecation of the `E_STRICT` constant in PHP 8.4
* Removed `.phpstorm.meta.php` file as methods such as `TestCase::createStub()` use generics / template types for their return types and PhpStorm, for example, uses that information

## [9.6.20] - 2024-07-10

### Changed

* Updated dependencies (so that users that install using Composer's `--prefer-lowest` CLI option also get recent versions)

## [9.6.19] - 2024-04-05

### Changed

* The namespaces of dependencies are now prefixed with `PHPUnitPHAR` instead of just `PHPUnit` for the PHAR distribution of PHPUnit

## [9.6.18] - 2024-03-21

### Changed

* [#5763](https://github.com/sebastianbergmann/phpunit/issues/5763): Release nullable type changes for PHPUnit 9.6

## [9.6.17] - 2024-02-23

### Changed

* Improve output of `--check-version` CLI option
* Improve description of `--check-version` CLI option
* Show help for `--manifest`, `--sbom`, and `--composer-lock` when the PHAR is used

### Fixed

* [#5712](https://github.com/sebastianbergmann/phpunit/issues/5712): Update dependencies for PHAR distribution of PHPUnit 9.6

## [9.6.16] - 2024-01-19

### Changed

* Make PHAR build reproducible (the only remaining differences were in the timestamps for the files in the PHAR)

### Fixed

* [#5516](https://github.com/sebastianbergmann/phpunit/issues/5516): Assertions that use the `LogicalNot` constraint (`assertNotEquals()`, `assertStringNotContainsString()`, ...) can generate confusing failure messages
* [#5666](https://github.com/sebastianbergmann/phpunit/issues/5666): `--no-extensions` CLI option does not work
* [#5673](https://github.com/sebastianbergmann/phpunit/issues/5673): Confusing error message when migration of a configuration is requested that does not need to be migrated

## [9.6.15] - 2023-12-01

### Fixed

* [#5596](https://github.com/sebastianbergmann/phpunit/issues/5596): `PHPUnit\Framework\TestCase` has `@internal` annotation in PHAR

## [9.6.14] - 2023-12-01

### Added

* [#5577](https://github.com/sebastianbergmann/phpunit/issues/5577): `--composer-lock` CLI option for PHAR binary that displays the `composer.lock` used to build the PHAR

## [9.6.13] - 2023-09-19

### Changed

* The child processes used for process isolation now use temporary files to communicate their result to the parent process

## [9.6.12] - 2023-09-12

### Changed

* [#5508](https://github.com/sebastianbergmann/phpunit/pull/5508): Generate code coverage report in PHP format as first in list to avoid serializing cache data

## [9.6.11] - 2023-08-19

### Added

* [#5478](https://github.com/sebastianbergmann/phpunit/pull/5478):  `assertObjectHasProperty()` and `assertObjectNotHasProperty()`

## [9.6.10] - 2023-07-10

### Changed

* [#5419](https://github.com/sebastianbergmann/phpunit/pull/5419): Allow empty `<extensions>` element in XML configuration

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

[9.6.34]: https://github.com/sebastianbergmann/phpunit/compare/9.6.33...9.6.34
[9.6.33]: https://github.com/sebastianbergmann/phpunit/compare/9.6.32...9.6.33
[9.6.32]: https://github.com/sebastianbergmann/phpunit/compare/9.6.31...9.6.32
[9.6.31]: https://github.com/sebastianbergmann/phpunit/compare/9.6.30...9.6.31
[9.6.30]: https://github.com/sebastianbergmann/phpunit/compare/9.6.29...9.6.30
[9.6.29]: https://github.com/sebastianbergmann/phpunit/compare/9.6.28...9.6.29
[9.6.28]: https://github.com/sebastianbergmann/phpunit/compare/9.6.27...9.6.28
[9.6.27]: https://github.com/sebastianbergmann/phpunit/compare/9.6.26...9.6.27
[9.6.26]: https://github.com/sebastianbergmann/phpunit/compare/9.6.25...9.6.26
[9.6.25]: https://github.com/sebastianbergmann/phpunit/compare/9.6.24...9.6.25
[9.6.24]: https://github.com/sebastianbergmann/phpunit/compare/9.6.23...9.6.24
[9.6.23]: https://github.com/sebastianbergmann/phpunit/compare/9.6.22...9.6.23
[9.6.22]: https://github.com/sebastianbergmann/phpunit/compare/9.6.21...9.6.22
[9.6.21]: https://github.com/sebastianbergmann/phpunit/compare/9.6.20...9.6.21
[9.6.20]: https://github.com/sebastianbergmann/phpunit/compare/9.6.19...9.6.20
[9.6.19]: https://github.com/sebastianbergmann/phpunit/compare/9.6.18...9.6.19
[9.6.18]: https://github.com/sebastianbergmann/phpunit/compare/9.6.17...9.6.18
[9.6.17]: https://github.com/sebastianbergmann/phpunit/compare/9.6.16...9.6.17
[9.6.16]: https://github.com/sebastianbergmann/phpunit/compare/9.6.15...9.6.16
[9.6.15]: https://github.com/sebastianbergmann/phpunit/compare/9.6.14...9.6.15
[9.6.14]: https://github.com/sebastianbergmann/phpunit/compare/9.6.13...9.6.14
[9.6.13]: https://github.com/sebastianbergmann/phpunit/compare/9.6.12...9.6.13
[9.6.12]: https://github.com/sebastianbergmann/phpunit/compare/9.6.11...9.6.12
[9.6.11]: https://github.com/sebastianbergmann/phpunit/compare/9.6.10...9.6.11
[9.6.10]: https://github.com/sebastianbergmann/phpunit/compare/9.6.9...9.6.10
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
