# Changes in PHPUnit 10.3

All notable changes of the PHPUnit 10.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [10.3.3] - 2023-09-05

### Fixed

* [#5484](https://github.com/sebastianbergmann/phpunit/issues/5484): TestDox name prettifier only supports backed enumerations
* [#5487](https://github.com/sebastianbergmann/phpunit/issues/5487): Tests are run more than once if `<testsuites>` has overlapping `<directory>`s
* [#5498](https://github.com/sebastianbergmann/phpunit/issues/5498): Hook methods are run in wrong order

## [10.3.2] - 2023-08-15

# Deprecated

* [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423): `TestCase::throwException()` is no longer deprecated
* [#5425](https://github.com/sebastianbergmann/phpunit/issues/5425): `InvocationMocker::willReturnOnConsecutiveCalls()` is no longer deprecated
* [#5444](https://github.com/sebastianbergmann/phpunit/issues/5444): `MockBuilder::setMockClassName()` is no longer deprecated

## [10.3.1] - 2023-08-04

### Fixed

* [#5459](https://github.com/sebastianbergmann/phpunit/issues/5459): `assertStringMatchesFormat()` should allow `string` type (not `non-empty-string`) for `$actual` parameter

## [10.3.0] - 2023-08-04

### Added

* [#5368](https://github.com/sebastianbergmann/phpunit/pull/5368): Control when PHP's garbage collector is triggered
* [#5428](https://github.com/sebastianbergmann/phpunit/issues/5428): Attribute `#[WithoutErrorHandler]` to disable PHPUnit's error handler for a test method
* [#5431](https://github.com/sebastianbergmann/phpunit/pull/5431): Add more garbage collector details to event telemetry

### Changed

* Errors (`E_USER_ERROR`), deprecations (`E_DEPRECATED`, `E_USER_DEPRECATED`), notices (`E_STRICT`, `E_NOTICE`, `E_USER_NOTICE`), and warnings (`E_WARNING`, `E_USER_WARNINGS`) are now displayed grouped by issue
* When a test case class inherits test methods from a parent class then, by default (when no test reordering is requested), the test methods from the class that is highest in the inheritance tree (instead of the class that is lowest in the inheritance tree) are now run first
* Invocation count expectation failure messages have been slightly improved
* When a test case class inherits test methods from a parent class then the TestDox output for such a test case class now starts with test methods from the class that is highest in the inheritance tree (instead of the class that is lowest in the inheritance tree)
* `TestCase::createStub()`, `TestCase::createStubForIntersectionOfInterfaces()`, and `TestCase::createConfiguredStub()` are now static (and can be used from static data provider methods)
* The internal methods `__phpunit_*()` have been removed from the `Stub` and `MockObject` interfaces

# Deprecated

* [#5214](https://github.com/sebastianbergmann/phpunit/issues/5214): Deprecate `TestCase::iniSet()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5216](https://github.com/sebastianbergmann/phpunit/issues/5216): Deprecate `TestCase::setLocale()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5421](https://github.com/sebastianbergmann/phpunit/issues/5421): Deprecate `MockBuilder::enableAutoReturnValueGeneration()` and `MockBuilder::disableAutoReturnValueGeneration()` (these methods only have a `@deprecated` annotation for now; using these methods will trigger a deprecation warning in PHPUnit 11; these methods will be removed in PHPUnit 12)
* [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423): Deprecate `TestCase::returnValue()`, `TestCase::onConsecutiveCalls()`, `TestCase::returnValueMap()`, `TestCase::returnArgument()`, `TestCase::returnSelf()`, `TestCase::returnCallback()`, and `TestCase::throwException()` (these methods only have a `@deprecated` annotation for now; using these methods will trigger a deprecation warning in PHPUnit 11; these methods will be removed in PHPUnit 12)
* [#5425](https://github.com/sebastianbergmann/phpunit/issues/5425): Deprecate `InvocationMocker::willReturnOnConsecutiveCalls()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)
* [#5444](https://github.com/sebastianbergmann/phpunit/issues/5444): Deprecate `MockBuilder::setMockClassName()` (this method only has a `@deprecated` annotation for now; using this method will trigger a deprecation warning in PHPUnit 11; this method will be removed in PHPUnit 12)

### Fixed

* [#5456](https://github.com/sebastianbergmann/phpunit/issues/5456): Risky Test Check for Output Buffering is performed before after-test methods are called

[10.3.3]: https://github.com/sebastianbergmann/phpunit/compare/10.3.2...10.3.3
[10.3.2]: https://github.com/sebastianbergmann/phpunit/compare/10.3.1...10.3.2
[10.3.1]: https://github.com/sebastianbergmann/phpunit/compare/10.3.0...10.3.1
[10.3.0]: https://github.com/sebastianbergmann/phpunit/compare/10.2.7...10.3.0
