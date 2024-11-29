# Changes in PHPUnit 11.5

All notable changes of the PHPUnit 11.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [11.5.0] - 2024-12-06

### Added

* [#5948](https://github.com/sebastianbergmann/phpunit/pull/5948): Support for Property Hooks in Test Doubles
* [#5954](https://github.com/sebastianbergmann/phpunit/issues/5954): Provide a way to stop execution at a particular deprecation
* [#5998](https://github.com/sebastianbergmann/phpunit/pull/5998): Do not run `SKIPIF` section of PHPT test in separate process when it is free of side effects
* [#5999](https://github.com/sebastianbergmann/phpunit/pull/5999): Do not run `CLEAN` section of PHPT test in separate process when it is free of side effects that modify the parent process
* `TestRunner\ChildProcessStarted` and `TestRunner\ChildProcessFinished` events

### Changed

* The type of the value passed for the `$type` parameter of the `assertContainsOnly()`, `assertNotContainsOnly()`, `isType()`, and `containsOnly()` methods can now be `NativeType`

### Deprecated

* [#6046](https://github.com/sebastianbergmann/phpunit/issues/6046): Support for using `assertContainsOnly()` (and `assertNotContainsOnly()`) with classes and interfaces
* Support for passing a value of type `string` for the `$type` parameter of the `assertContainsOnly()`, `assertNotContainsOnly()`, `isType()`, and `containsOnly()` methods (use `NativeType` instead)

[11.5.0]: https://github.com/sebastianbergmann/phpunit/compare/11.4...11.5
