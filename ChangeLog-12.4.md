# Changes in PHPUnit 12.4

All notable changes of the PHPUnit 12.4 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.4.3] - 2025-11-13

### Fixed

* [#6402](https://github.com/sebastianbergmann/phpunit/pull/6402): Avoid reading from `STDOUT` when `rewind()` fails

## [12.4.2] - 2025-10-30

### Changed

* Skipped tests alone no longer lead to a yellow background for the test result summary

### Fixed

* [#6391](https://github.com/sebastianbergmann/phpunit/issues/6391): Errors during backup of global variables and static properties are not reported

## [12.4.1] - 2025-10-09

### Fixed

* [#6364](https://github.com/sebastianbergmann/phpunit/issues/6364): `--filter` format used by PhpStorm stopped working
* [#6378](https://github.com/sebastianbergmann/phpunit/issues/6378): Wrong method name passed to `DataProviderMethodCalled` event value object

## [12.4.0] - 2025-10-03

### Added

* [#6277](https://github.com/sebastianbergmann/phpunit/issues/6277): Allow tests to opt out of the validation that a data provider method does not provide data sets with more values than a test method accepts
* [#6341](https://github.com/sebastianbergmann/phpunit/pull/6341): Support for regular expressions with `#[IgnoreDeprecations]` attribute
* [#6354](https://github.com/sebastianbergmann/phpunit/issues/6354): Invokable constraints

### Deprecated

* [#6284](https://github.com/sebastianbergmann/phpunit/issues/6284): `#[RunClassInSeparateProcess]` attribute
* [#6355](https://github.com/sebastianbergmann/phpunit/issues/6355): Support for version constraint string argument without explicit version comparison operator

[12.4.3]: https://github.com/sebastianbergmann/phpunit/compare/12.4.2...12.4.3
[12.4.2]: https://github.com/sebastianbergmann/phpunit/compare/12.4.1...12.4.2
[12.4.1]: https://github.com/sebastianbergmann/phpunit/compare/12.4.0...12.4.1
[12.4.0]: https://github.com/sebastianbergmann/phpunit/compare/12.3.15...12.4.0
