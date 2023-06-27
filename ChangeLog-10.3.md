# Changes in PHPUnit 10.3

All notable changes of the PHPUnit 10.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [10.3.0] - 2023-08-04

### Added

* [#5428](https://github.com/sebastianbergmann/phpunit/issues/5428): Attribute `#[WithoutErrorHandler]` to disable PHPUnit's error handler for a test method
* [#5431](https://github.com/sebastianbergmann/phpunit/pull/5431): Add more garbage collector details to event telemetry

### Changed

* When a test case class inherits test methods from a parent class then, by default (when no test reordering is requested), the test methods from the class that is highest in the inheritance tree (instead of the class that is lowest in the inheritance tree) are now run first
* Invocation count expectation failure messages have been slightly improved
* When a test case class inherits test methods from a parent class then the TestDox output for such a test case class now starts with test methods from the class that is highest in the inheritance tree (instead of the class that is lowest in the inheritance tree)
* `TestCase::createStub()`, `TestCase::createStubForIntersectionOfInterfaces()`, and `TestCase::createConfiguredStub()` are now static (and can be used from static data provider methods)
* The internal methods `__phpunit_*()` have been removed from the `Stub` and `MockObject` interfaces

[10.3.0]: https://github.com/sebastianbergmann/phpunit/compare/10.2...main
