# Changes in PHPUnit 11.1

All notable changes of the PHPUnit 11.1 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [11.1.0] - 2024-04-05

### Added

* [#5175](https://github.com/sebastianbergmann/phpunit/issues/5175): `#[CoversMethod]` and `#[UsesMethod]` attributes for more fine-grained code coverage targeting
* [#5696](https://github.com/sebastianbergmann/phpunit/pull/5696): `#[DisableReturnValueGenerationForTestDoubles]` attribute for disabling return value generation for test doubles created using `createMock()`, `createMockForIntersectionOfInterfaces()`, `createPartialMock()`, `createStub()`, and `createStubForIntersectionOfInterfaces()`

### Changed

* [#5708](https://github.com/sebastianbergmann/phpunit/issues/5708): Allow the `--group`, `--exclude-group`, `--covers`, `--uses`, and `--test-suffix` CLI options to be used multiple times

### Deprecated

* [#5709](https://github.com/sebastianbergmann/phpunit/issues/5709): Deprecate support for using comma-separated values with the `--group`, `--exclude-group`, `--covers`, `--uses`, and `--test-suffix` CLI options

[11.1.0]: https://github.com/sebastianbergmann/phpunit/compare/11.0...main
