# Changes in PHPUnit 10.1

All notable changes of the PHPUnit 10.1 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [10.1.0] - 2023-04-07

### Added

* [#5201](https://github.com/sebastianbergmann/phpunit/issues/5201): Add `TestCase::registerFailureType()` to register interface that marks exceptions to be treated as failures, not errors

### Changed

* [#5236](https://github.com/sebastianbergmann/phpunit/issues/5236): Deprecate the `CodeCoverageIgnore` attribute
* [#5239](https://github.com/sebastianbergmann/phpunit/issues/5239): Deprecate `TestCase::createPartialMock()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5240](https://github.com/sebastianbergmann/phpunit/issues/5240): Deprecate `TestCase::createTestProxy()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5241](https://github.com/sebastianbergmann/phpunit/issues/5241): Deprecate `TestCase::getMockForAbstractClass()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)

[10.1.0]: https://github.com/sebastianbergmann/phpunit/compare/10.0...main
