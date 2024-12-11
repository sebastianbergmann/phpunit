# Changes in PHPUnit 11.5

All notable changes of the PHPUnit 11.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [11.5.1] - 2024-12-11

### Added

* [#6081](https://github.com/sebastianbergmann/phpunit/pull/6081): `DefaultResultCache::mergeWith()` for merging result cache instances

### Fixed

* [#6066](https://github.com/sebastianbergmann/phpunit/pull/6066): TeamCity logger does not handle error/skipped events in before-class methods correctly

## [11.5.0] - 2024-12-06

### Added

* [#5948](https://github.com/sebastianbergmann/phpunit/pull/5948): Support for Property Hooks in Test Doubles
* [#5954](https://github.com/sebastianbergmann/phpunit/issues/5954): Provide a way to stop execution at a particular deprecation
* Method `assertContainsNotOnlyInstancesOf()` in the `PHPUnit\Framework\Assert` class as the inverse of the `assertContainsOnlyInstancesOf()` method
* Methods `assertContainsOnlyArray()`, `assertContainsOnlyBool()`, `assertContainsOnlyCallable()`, `assertContainsOnlyFloat()`, `assertContainsOnlyInt()`, `assertContainsOnlyIterable()`, `assertContainsOnlyNull()`, `assertContainsOnlyNumeric()`, `assertContainsOnlyObject()`, `assertContainsOnlyResource()`, `assertContainsOnlyClosedResource()`, `assertContainsOnlyScalar()`, and `assertContainsOnlyString()` in the `PHPUnit\Framework\Assert` class as specialized alternatives for the generic `assertContainsOnly()` method
* Methods `assertContainsNotOnlyArray()`, `assertContainsNotOnlyBool()`, `assertContainsNotOnlyCallable()`, `assertContainsNotOnlyFloat()`, `assertContainsNotOnlyInt()`, `assertContainsNotOnlyIterable()`, `assertContainsNotOnlyNull()`, `assertContainsNotOnlyNumeric()`, `assertContainsNotOnlyObject()`, `assertContainsNotOnlyResource()`, `assertContainsNotOnlyClosedResource()`, `assertContainsNotOnlyScalar()`, and `assertContainsNotOnlyString()` in the `PHPUnit\Framework\Assert` class as specialized alternatives for the generic `assertNotContainsOnly()` method
* Methods `containsOnlyArray()`, `containsOnlyBool()`, `containsOnlyCallable()`, `containsOnlyFloat()`, `containsOnlyInt()`, `containsOnlyIterable()`, `containsOnlyNull()`, `containsOnlyNumeric()`, `containsOnlyObject()`, `containsOnlyResource()`, `containsOnlyClosedResource()`, `containsOnlyScalar()`, and `containsOnlyString()` in the `PHPUnit\Framework\Assert` class as specialized alternatives for the generic `containsOnly()` method
* Methods `isArray()`, `isBool()`, `isCallable()`, `isFloat()`, `isInt()`, `isIterable()`, `isNumeric()`, `isObject()`, `isResource()`, `isClosedResource()`, `isScalar()`, `isString()` in the `PHPUnit\Framework\Assert` class as specialized alternatives for the generic `isType()` method
* `TestRunner\ChildProcessStarted` and `TestRunner\ChildProcessFinished` events

### Changed

* [#5998](https://github.com/sebastianbergmann/phpunit/pull/5998): Do not run `SKIPIF` section of PHPT test in separate process when it is free of side effects
* [#5999](https://github.com/sebastianbergmann/phpunit/pull/5999): Do not run `CLEAN` section of PHPT test in separate process when it is free of side effects that modify the parent process

### Deprecated

* [#6052](https://github.com/sebastianbergmann/phpunit/issues/6052): `isType()` (use `isArray()`, `isBool()`, `isCallable()`, `isFloat()`, `isInt()`, `isIterable()`, `isNull()`, `isNumeric()`, `isObject()`, `isResource()`, `isClosedResource()`, `isScalar()`, or `isString()` instead)
* [#6055](https://github.com/sebastianbergmann/phpunit/issues/6055): `assertContainsOnly()` (use `assertContainsOnlyArray()`, `assertContainsOnlyBool()`, `assertContainsOnlyCallable()`, `assertContainsOnlyFloat()`, `assertContainsOnlyInt()`, `assertContainsOnlyIterable()`, `assertContainsOnlyNumeric()`, `assertContainsOnlyObject()`, `assertContainsOnlyResource()`, `assertContainsOnlyClosedResource()`, `assertContainsOnlyScalar()`, or `assertContainsOnlyString()` instead)
* [#6055](https://github.com/sebastianbergmann/phpunit/issues/6055): `assertNotContainsOnly()` (use `assertContainsNotOnlyArray()`, `assertContainsNotOnlyBool()`, `assertContainsNotOnlyCallable()`, `assertContainsNotOnlyFloat()`, `assertContainsNotOnlyInt()`, `assertContainsNotOnlyIterable()`, `assertContainsNotOnlyNumeric()`, `assertContainsNotOnlyObject()`, `assertContainsNotOnlyResource()`, `assertContainsNotOnlyClosedResource()`, `assertContainsNotOnlyScalar()`, or `assertContainsNotOnlyString()` instead)
* [#6059](https://github.com/sebastianbergmann/phpunit/issues/6059): `containsOnly()` (use `containsOnlyArray()`, `containsOnlyBool()`, `containsOnlyCallable()`, `containsOnlyFloat()`, `containsOnlyInt()`, `containsOnlyIterable()`, `containsOnlyNumeric()`, `containsOnlyObject()`, `containsOnlyResource()`, `containsOnlyClosedResource()`, `containsOnlyScalar()`, or `containsOnlyString()` instead)

[11.5.1]: https://github.com/sebastianbergmann/phpunit/compare/11.5.0...11.5.1
[11.5.0]: https://github.com/sebastianbergmann/phpunit/compare/11.4.4...11.5.0
