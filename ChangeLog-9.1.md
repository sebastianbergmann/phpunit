# Changes in PHPUnit 9.1

All notable changes of the PHPUnit 9.1 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.1.4] - 2020-04-30

* [#4196](https://github.com/sebastianbergmann/phpunit/issues/4196): Invalid `@covers` annotation crashes test runner

## [9.1.3] - 2020-04-23

### Added

* [#4186](https://github.com/sebastianbergmann/phpunit/issues/4186): Support adding directories to blacklist (of files that PHPUnit filters from stacktraces)

### Changed

* `PHPUnit\Util\Blacklist` is no longer `@internal`

## [9.1.2] - 2020-04-20

### Changed

* Changed how `PHPUnit\TextUI\Command` passes warnings to `PHPUnit\TextUI\TestRunner`

## [9.1.1] - 2020-04-03

### Fixed

* [#4162](https://github.com/sebastianbergmann/phpunit/issues/4162): Raising an exception from a test double's configured method does not work

## [9.1.0] - 2020-04-03

### Added

* [#4061](https://github.com/sebastianbergmann/phpunit/issues/4061): Implement `assertIsNotReadable()` as alternative for `assertNotIsReadable()` with a more readable name
* [#4064](https://github.com/sebastianbergmann/phpunit/issues/4064): Implement `assertIsNotWritable()` as alternative for `assertNotIsWritable()` with a more readable name
* [#4067](https://github.com/sebastianbergmann/phpunit/issues/4067): Implement `assertDirectoryDoesNotExist()` as alternative for `assertDirectoryNotExists()` with a more readable name
* [#4070](https://github.com/sebastianbergmann/phpunit/issues/4070): Implement `assertDirectoryIsNotReadable()` as alternative for `assertDirectoryNotIsReadable()` with a more readable name
* [#4073](https://github.com/sebastianbergmann/phpunit/issues/4073): Implement `assertDirectoryIsNotWritable()` as alternative for `assertDirectoryNotIsWritable()` with a more readable name
* [#4076](https://github.com/sebastianbergmann/phpunit/issues/4076): Implement `assertFileDoesNotExist()` as alternative for `assertFileNotExists()` with a more readable name
* [#4079](https://github.com/sebastianbergmann/phpunit/issues/4079): Implement `assertFileIsNotReadable()` as alternative for `assertFileNotIsReadable()` with a more readable name
* [#4082](https://github.com/sebastianbergmann/phpunit/issues/4082): Implement `assertFileIsNotWritable()` as alternative for `assertFileNotIsWritable()` with a more readable name
* [#4085](https://github.com/sebastianbergmann/phpunit/issues/4085): Implement `assertMatchesRegularExpression()` as alternative for `assertRegExp()` with a more readable name
* [#4088](https://github.com/sebastianbergmann/phpunit/issues/4088): Implement `assertDoesNotMatchRegularExpression()` as alternative for `assertNotRegExp()` with a more readable name
* [#4100](https://github.com/sebastianbergmann/phpunit/issues/4100): Implement `failOnIncomplete` and `failOnSkipped` configuration options as well as `--fail-on-incomplete` and `--fail-on-skipped` commandline options
* [#4130](https://github.com/sebastianbergmann/phpunit/pull/4130): Canonicalize JSON values in failure message
* [#4136](https://github.com/sebastianbergmann/phpunit/pull/4136): Allow loading PHPUnit extensions via command-line options
* [#4148](https://github.com/sebastianbergmann/phpunit/issues/4148): Support for `@preCondition` and `@postCondition` annotations

### Changed

* [#4039](https://github.com/sebastianbergmann/phpunit/issues/4039): Deprecate custom test suite loader
* [#4062](https://github.com/sebastianbergmann/phpunit/issues/4062): Deprecate `assertNotIsReadable()`
* [#4065](https://github.com/sebastianbergmann/phpunit/issues/4065): Deprecate `assertNotIsWritable()`
* [#4068](https://github.com/sebastianbergmann/phpunit/issues/4068): Deprecate `assertDirectoryNotExists()`
* [#4071](https://github.com/sebastianbergmann/phpunit/issues/4071): Deprecate `assertDirectoryNotIsReadable()`
* [#4074](https://github.com/sebastianbergmann/phpunit/issues/4074): Deprecate `assertDirectoryNotIsWritable()`
* [#4077](https://github.com/sebastianbergmann/phpunit/issues/4077): Deprecate `assertFileNotExists()`
* [#4080](https://github.com/sebastianbergmann/phpunit/issues/4080): Deprecate `assertFileNotIsReadable()`
* [#4083](https://github.com/sebastianbergmann/phpunit/issues/4083): Deprecate `assertFileNotIsWritable()`
* [#4086](https://github.com/sebastianbergmann/phpunit/issues/4086): Deprecate `assertRegExp()`
* [#4089](https://github.com/sebastianbergmann/phpunit/issues/4089): Deprecate `assertNotRegExp()`
* [#4091](https://github.com/sebastianbergmann/phpunit/issues/4091): Deprecate `assertEqualXMLStructure()`
* [#4095](https://github.com/sebastianbergmann/phpunit/pull/4095): Improve performance of `StringContains` constraint
* [#4105](https://github.com/sebastianbergmann/phpunit/issues/4105): Deprecate multiple test case classes in single file and test case class name differing from filename
* [#4141](https://github.com/sebastianbergmann/phpunit/pull/4141): Deprecate Prophecy integration

[9.1.4]: https://github.com/sebastianbergmann/phpunit/compare/9.1.3...9.1.4
[9.1.3]: https://github.com/sebastianbergmann/phpunit/compare/9.1.2...9.1.3
[9.1.2]: https://github.com/sebastianbergmann/phpunit/compare/9.1.1...9.1.2
[9.1.1]: https://github.com/sebastianbergmann/phpunit/compare/9.1.0...9.1.1
[9.1.0]: https://github.com/sebastianbergmann/phpunit/compare/9.0.2...9.1.0
