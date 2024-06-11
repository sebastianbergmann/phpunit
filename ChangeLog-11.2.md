# Changes in PHPUnit 11.2

All notable changes of the PHPUnit 11.2 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

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

[11.2.1]: https://github.com/sebastianbergmann/phpunit/compare/11.2.0...11.2.1
[11.2.0]: https://github.com/sebastianbergmann/phpunit/compare/11.1.3...11.2.0
