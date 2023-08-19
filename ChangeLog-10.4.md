# Changes in PHPUnit 10.4

All notable changes of the PHPUnit 10.4 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [10.4.0] - 2023-10-06

### Added

* [#5471](https://github.com/sebastianbergmann/phpunit/issues/5471): `assertFileMatchesFormat()` and `assertFileMatchesFormatFile()`

### Deprecated

* [#5472](https://github.com/sebastianbergmann/phpunit/issues/5472): `TestCase::assertStringNotMatchesFormat()` and `TestCase::assertStringNotMatchesFormatFile()` (these methods only have a `@deprecated` annotation for now; using these methods will trigger a deprecation warning in PHPUnit 11; these methods will be removed in PHPUnit 12)

[10.4.0]: https://github.com/sebastianbergmann/phpunit/compare/10.3...main
