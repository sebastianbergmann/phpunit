# Changes in PHPUnit 11.2

All notable changes of the PHPUnit 11.2 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [11.2.5] - 2024-06-20

### Changed

* [#5877](https://github.com/sebastianbergmann/phpunit/pull/5877): Use `array_pop()` instead of `array_shift()` for processing `Test` objects in `TestSuite::run()` and optimize `TestSuite::isEmpty()`

## [11.2.4] - 2024-06-20

### Changed

* [#5875](https://github.com/sebastianbergmann/phpunit/pull/5875): Also destruct `TestCase` objects early that use a data provider

## [11.2.3] - 2024-06-19

### Changed

* [#5870](https://github.com/sebastianbergmann/phpunit/pull/5870): Do not collect unnecessary information using `debug_backtrace()`

## [11.2.2] - 2024-06-15

### Changed

* [#5861](https://github.com/sebastianbergmann/phpunit/pull/5861): Destroy `TestCase` object after its test was run

### Fixed

* [#5822](https://github.com/sebastianbergmann/phpunit/pull/5822): PHP deprecations triggered within a closure are not handled correctly

## [11.2.1] - 2024-06-11

### Fixed

* [#5857](https://github.com/sebastianbergmann/phpunit/issues/5857): Mocked methods cannot be called from the original constructor of a partially mocked class
* [#5859](https://github.com/sebastianbergmann/phpunit/issues/5859): XML Configuration File Migrator does not remove `cacheDirectory` attribute from `<coverage>` element when migrating from PHPUnit 11.1 to PHPUnit 11.2

## [11.2.0] - 2024-06-07

### Added

* [#5799](https://github.com/sebastianbergmann/phpunit/issues/5799): `#[CoversTrait]` and `#[UsesTrait]` attributes
* [#5804](https://github.com/sebastianbergmann/phpunit/pull/5804): Support doubling `readonly` classes
* [#5811](https://github.com/sebastianbergmann/phpunit/issues/5811): `assertObjectNotEquals()`

### Deprecated

* [#5800](https://github.com/sebastianbergmann/phpunit/issues/5800): Support for targeting traits with `#[CoversClass]` and `#[UsesClass]` attributes

[11.2.5]: https://github.com/sebastianbergmann/phpunit/compare/11.2.4...11.2.5
[11.2.4]: https://github.com/sebastianbergmann/phpunit/compare/11.2.3...11.2.4
[11.2.3]: https://github.com/sebastianbergmann/phpunit/compare/11.2.2...11.2.3
[11.2.2]: https://github.com/sebastianbergmann/phpunit/compare/11.2.1...11.2.2
[11.2.1]: https://github.com/sebastianbergmann/phpunit/compare/11.2.0...11.2.1
[11.2.0]: https://github.com/sebastianbergmann/phpunit/compare/11.1.3...11.2.0
