# Changes in PHPUnit 9.2

All notable changes of the PHPUnit 9.2 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.2.5] - 2020-06-22

### Fixed

* [#4312](https://github.com/sebastianbergmann/phpunit/issues/4312): Fix for [#4299](https://github.com/sebastianbergmann/phpunit/issues/4299) breaks backward compatibility

## [9.2.4] - 2020-06-21

### Fixed

* [#4291](https://github.com/sebastianbergmann/phpunit/issues/4291): [#4258](https://github.com/sebastianbergmann/phpunit/pull/4258) breaks backward compatibility
* [#4299](https://github.com/sebastianbergmann/phpunit/issues/4299): "No tests executed" does not always result in exit code `1`
* [#4306](https://github.com/sebastianbergmann/phpunit/issues/4306): Exceptions during code coverage driver initialization are not handled correctly

## [9.2.3] - 2020-06-15

### Fixed

* [#4211](https://github.com/sebastianbergmann/phpunit/issues/4211): `phpdbg_*()` functions are scoped to `PHPUnit\phpdbg_*()`

## [9.2.2] - 2020-06-07

### Changed

* Improved message of exception that is raised when multiple matchers can be applied to a test double invocation

### Fixed

* Fixed default values for `lowUpperBound` and `highLowerBound` in `phpunit.xsd`

## [9.2.1] - 2020-06-05

### Fixed

* [#4269](https://github.com/sebastianbergmann/phpunit/issues/4269): Test with `@coversNothing` annotation is wrongly marked as risky with `forceCoversAnnotation="true"`

## [9.2.0] - 2020-06-05

### Added

* [#4224](https://github.com/sebastianbergmann/phpunit/issues/4224): Support for Union Types for test double code generation

### Changed

* [#4246](https://github.com/sebastianbergmann/phpunit/issues/4246): Tests that are supposed to have a `@covers` annotation are now marked as risky even if code coverage is not collected
* [#4258](https://github.com/sebastianbergmann/phpunit/pull/4258): Prevent unpredictable result by raising an exception when multiple matchers can be applied to a test double invocation
* The test runner no longer relies on `$_SERVER['REQUEST_TIME_FLOAT']` for printing the elapsed time

[9.2.5]: https://github.com/sebastianbergmann/phpunit/compare/9.2.4...9.2.5
[9.2.4]: https://github.com/sebastianbergmann/phpunit/compare/9.2.3...9.2.4
[9.2.3]: https://github.com/sebastianbergmann/phpunit/compare/9.2.2...9.2.3
[9.2.2]: https://github.com/sebastianbergmann/phpunit/compare/9.2.1...9.2.2
[9.2.1]: https://github.com/sebastianbergmann/phpunit/compare/9.2.0...9.2.1
[9.2.0]: https://github.com/sebastianbergmann/phpunit/compare/9.1.5...9.2.0
