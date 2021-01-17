# Changes in PHPUnit 9.5

All notable changes of the PHPUnit 9.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.5.1] - 2021-01-17

### Fixed

* [#4572](https://github.com/sebastianbergmann/phpunit/issues/4572): Schema validation does not work with `%xx` sequences in path to `phpunit.xsd`

## [9.5.0] - 2020-12-04

### Changed

* [#4490](https://github.com/sebastianbergmann/phpunit/issues/4490): Emit Error instead of Warning when test case class cannot be instantiated
* [#4491](https://github.com/sebastianbergmann/phpunit/issues/4491): Emit Error instead of Warning when data provider does not work correctly
* [#4492](https://github.com/sebastianbergmann/phpunit/issues/4492): Emit Error instead of Warning when test double configuration is invalid
* [#4493](https://github.com/sebastianbergmann/phpunit/issues/4493): Emit error when (configured) test directory does not exist

### Fixed

* [#4535](https://github.com/sebastianbergmann/phpunit/issues/4535): `getMockFromWsdl()` does not handle methods that do not have parameters correctly

[9.5.1]: https://github.com/sebastianbergmann/phpunit/compare/9.5.0...9.5.1
[9.5.0]: https://github.com/sebastianbergmann/phpunit/compare/9.4.4...9.5.0
