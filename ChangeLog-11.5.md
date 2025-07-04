# Changes in PHPUnit 11.5

All notable changes of the PHPUnit 11.5 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [11.5.26] - 2025-07-04

### Fixed

* [#6104](https://github.com/sebastianbergmann/phpunit/issues/6104): Test with dependencies and data provider fails
* [#6163](https://github.com/sebastianbergmann/phpunit/issues/6163): `@no-named-arguments` leads to static analysis errors for variadic arguments

## [11.5.25] - 2025-06-27

### Fixed

* [#6249](https://github.com/sebastianbergmann/phpunit/issues/6249): No meaningful error when `<testsuite>` element is missing required `name` attribute

## [11.5.24] - 2025-06-20

### Added

* [#6236](https://github.com/sebastianbergmann/phpunit/issues/6236): `failOnPhpunitWarning` attribute on the `<phpunit>` element of the XML configuration file and `--fail-on-phpunit-warning` CLI option for controlling whether PHPUnit should fail on PHPUnit warnings (default: `true`)
* [#6239](https://github.com/sebastianbergmann/phpunit/issues/6239): `--do-not-fail-on-deprecation`, `--do-not-fail-on-phpunit-warning`, `--do-not-fail-on-phpunit-deprecation`, `--do-not-fail-on-empty-test-suite`, `--do-not-fail-on-incomplete`, `--do-not-fail-on-notice`, `--do-not-fail-on-risky`, `--do-not-fail-on-skipped`, and `--do-not-fail-on-warning` CLI options
* `--do-not-report-useless-tests` CLI option as a replacement for `--dont-report-useless-tests`

### Deprecated

* `--dont-report-useless-tests` CLI option (use `--do-not-report-useless-tests` instead)

### Fixed

* [#6243](https://github.com/sebastianbergmann/phpunit/issues/6243): Constraints cannot be implemented without using internal class `ExpectationFailedException`

## [11.5.23] - 2025-06-13

### Fixed

* [#6222](https://github.com/sebastianbergmann/phpunit/issues/6222): Data Provider seems to mess up Test Dependencies
* `shortenArraysForExportThreshold` XML configuration setting has no effect on all arrays exported for event-related value objects

## [11.5.22] - 2025-06-06

### Changed

* Do not treat warnings differently than other issues in summary section of default output

## [11.5.21] - 2025-05-21

### Changed

* [#6210](https://github.com/sebastianbergmann/phpunit/pull/6210): Set default Clover coverage project name
* [#6217](https://github.com/sebastianbergmann/phpunit/pull/6217): Improve the error message when `createStubForIntersectionOfInterfaces()` is called with a class

## [11.5.20] - 2025-05-11

### Fixed

* [#6192](https://github.com/sebastianbergmann/phpunit/issues/6192): Reverted change made in PHPUnit 11.5.19 due to regression
* [#6199](https://github.com/sebastianbergmann/phpunit/issues/6199): `assertEmpty()` and `assertNotEmpty()` use overly restrictive `phpstan-assert empty` directives

## [11.5.19] - 2025-05-02

### Added

* `displayDetailsOnAllIssues` attribute on the `<phpunit>` element of the XML configuration file and `--display-all-issues` CLI option for controlling whether PHPUnit should display details on all issues that are triggered (default: `false`)
* `failOnAllIssues` attribute on the `<phpunit>` element of the XML configuration file and `--fail-on-all-issues` CLI option for controlling whether PHPUnit should fail on all issues that are triggered (default: `false`)

### Changed

* [#5956](https://github.com/sebastianbergmann/phpunit/issues/5956): Improved handling of deprecated `E_STRICT` constant

### Fixed

* [#6192](https://github.com/sebastianbergmann/phpunit/issues/6192): Positive `%a` and `%A` matches are not ignored from diff when `EXPECTF` fails

## [11.5.18] - 2025-04-22

### Changed

* When gathering the telemetry information that each event has, the real size of memory allocated from the operating system is no longer used as this is grown by PHP's memory manager in chunks that are so large that small(er) increases in peak memory usage cannot be seen
* The peak memory usage returned by `memory_get_peak_usage()` is now reset immediately before the `Test\Prepared` event is emitted using `memory_reset_peak_usage()` so that (memory usage at `Test\Finished` - memory usage at `Test\Prepared`) is a better approximation of the memory usage of the test
* The string representation of `Telemetry\Info` now uses peak memory usage instead of memory usage (this affects `--log-events-verbose-text`) 

### Fixed

* A "Before Test Method Errored" event is no longer emitted when a test is skipped in a "before test" method

## [11.5.17] - 2025-04-08

### Fixed

* [#6104](https://github.com/sebastianbergmann/phpunit/issues/6104): Reverted change introduced in PHPUnit 11.5.16

## [11.5.16] - 2025-04-08

### Fixed

* [#6104](https://github.com/sebastianbergmann/phpunit/issues/6104): Test with dependencies and data provider fails
* [#6174](https://github.com/sebastianbergmann/phpunit/issues/6174): `willReturnMap()` fails with nullable parameters when their default is `null` and no argument is passed for them

## [11.5.15] - 2025-03-23

### Changed

* [#6150](https://github.com/sebastianbergmann/phpunit/issues/6150): Reverted change introduced in PHPUnit 11.5.13

## [11.5.14] - 2025-03-19

### Changed

* Updated dependencies for PHAR distribution

## [11.5.13] - 2025-03-18

### Changed

* [#6150](https://github.com/sebastianbergmann/phpunit/issues/6150): Trigger warning when code coverage analysis is performed and no cache directory is configured

## [11.5.12] - 2025-03-07

### Fixed

* [#5976](https://github.com/sebastianbergmann/phpunit/issues/5976): TestDox result printer does not display details about errors triggered in before-first-test and after-last-test methods

## [11.5.11] - 2025-03-05

### Fixed

* [#6142](https://github.com/sebastianbergmann/phpunit/issues/6142): `$expected` and `$actual` are mixed up in failure description when `assertJsonFileEqualsJsonFile()` fails

## [11.5.10] - 2025-02-25

### Fixed

* [#6138](https://github.com/sebastianbergmann/phpunit/issues/6138): Test with failed expectation on value passed to mocked method is incorrectly considered risky

## [11.5.9] - 2025-02-21

### Fixed

* [#6134](https://github.com/sebastianbergmann/phpunit/issues/6134): Missing event when child process ends unexpectedly

## [11.5.8] - 2025-02-18

### Fixed

* A `Test\PreparationFailed` event is now emitted in addition to a `Test\Errored` event when an unexpected exception is triggered in a before-test method
* A `Test\Passed` event is no longer emitted in addition to a `Test\Failed` or `Test\Errored` event when an assertion failure or an unexpected exception is triggered in an after-test method  
* A `TestSuite\Finished` event is now emitted when a before-first-test method errors

## [11.5.7] - 2025-02-06

### Changed

* [#5951](https://github.com/sebastianbergmann/phpunit/issues/5951): The `includeUncoveredFiles` configuration option is no longer deprecated
* [#6117](https://github.com/sebastianbergmann/phpunit/issues/6117): Include source location information for issues triggered during test in `--debug` output
* [#6119](https://github.com/sebastianbergmann/phpunit/issues/6119): Improve message for errors that occur while parsing attributes
* [#6120](https://github.com/sebastianbergmann/phpunit/issues/6120): Allow negative priorities for hook methods

## [11.5.6] - 2025-01-31

### Changed

* [#6112](https://github.com/sebastianbergmann/phpunit/pull/6112): Improve performance of `SourceMapper`

### Fixed

* [#6115](https://github.com/sebastianbergmann/phpunit/issues/6115): Backed enumerations with values not of type `string` cannot be used in customized TestDox output

## [11.5.5] - 2025-01-29

### Changed

* Do not skip execution of test that depends on a test that is larger than itself

## [11.5.4] - 2025-01-28

### Changed

* [#5958](https://github.com/sebastianbergmann/phpunit/issues/5958): Support for `#[CoversTrait]` and `#[UsesTrait]` attributes is no longer deprecated
* [#5960](https://github.com/sebastianbergmann/phpunit/issues/5960): Support for targeting trait methods with the `#[CoversMethod]` and `#[UsesMethod]` attributes is no longer deprecated

### Fixed

* [#6103](https://github.com/sebastianbergmann/phpunit/issues/6103): Output from test run in separate process is printed twice
* [#6109](https://github.com/sebastianbergmann/phpunit/issues/6109): Skipping a test in a before-class method crashes JUnit XML logger
* [#6111](https://github.com/sebastianbergmann/phpunit/issues/6111): Deprecations cause `SourceMapper` to scan all `<source/>` files

## [11.5.3] - 2025-01-13

### Added

* `Test\AfterLastTestMethodErrored`, `Test\AfterTestMethodErrored`, `Test\BeforeTestMethodErrored`, `Test\PostConditionErrored`, and `Test\PreConditionErrored` events

### Fixed

* [#6093](https://github.com/sebastianbergmann/phpunit/issues/6093): Test Double Code Generator does not work when PHPUnit is used from PHAR on PHP 8.4
* [#6094](https://github.com/sebastianbergmann/phpunit/issues/6094): Errors in after-last-test methods are not reported
* [#6095](https://github.com/sebastianbergmann/phpunit/issues/6095): Expectation is not counted correctly when a doubled method is called more often than is expected
* [#6096](https://github.com/sebastianbergmann/phpunit/issues/6096): `--list-tests-xml` is broken when a group with a numeric name is defined
* [#6098](https://github.com/sebastianbergmann/phpunit/issues/6098): No `system-out` element in JUnit XML logfile
* [#6100](https://github.com/sebastianbergmann/phpunit/issues/6100): Suppressed deprecations incorrectly stop test execution when execution should be stopped on deprecation

## [11.5.2] - 2024-12-21

### Fixed

* [#6082](https://github.com/sebastianbergmann/phpunit/issues/6082): `assertArrayHasKey()`, `assertArrayNotHasKey()`, `arrayHasKey()`, and `ArrayHasKey::__construct()` do not support all possible key types
* [#6087](https://github.com/sebastianbergmann/phpunit/issues/6087): `--migrate-configuration` does not remove `beStrictAboutTodoAnnotatedTests` attribute from XML configuration file

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

[11.5.26]: https://github.com/sebastianbergmann/phpunit/compare/11.5.25...11.5.26
[11.5.25]: https://github.com/sebastianbergmann/phpunit/compare/11.5.24...11.5.25
[11.5.24]: https://github.com/sebastianbergmann/phpunit/compare/11.5.23...11.5.24
[11.5.23]: https://github.com/sebastianbergmann/phpunit/compare/11.5.22...11.5.23
[11.5.22]: https://github.com/sebastianbergmann/phpunit/compare/11.5.21...11.5.22
[11.5.21]: https://github.com/sebastianbergmann/phpunit/compare/11.5.20...11.5.21
[11.5.20]: https://github.com/sebastianbergmann/phpunit/compare/11.5.19...11.5.20
[11.5.19]: https://github.com/sebastianbergmann/phpunit/compare/11.5.18...11.5.19
[11.5.18]: https://github.com/sebastianbergmann/phpunit/compare/11.5.17...11.5.18
[11.5.17]: https://github.com/sebastianbergmann/phpunit/compare/11.5.16...11.5.17
[11.5.16]: https://github.com/sebastianbergmann/phpunit/compare/11.5.15...11.5.16
[11.5.15]: https://github.com/sebastianbergmann/phpunit/compare/11.5.14...11.5.15
[11.5.14]: https://github.com/sebastianbergmann/phpunit/compare/11.5.13...11.5.14
[11.5.13]: https://github.com/sebastianbergmann/phpunit/compare/11.5.12...11.5.13
[11.5.12]: https://github.com/sebastianbergmann/phpunit/compare/11.5.11...11.5.12
[11.5.11]: https://github.com/sebastianbergmann/phpunit/compare/11.5.10...11.5.11
[11.5.10]: https://github.com/sebastianbergmann/phpunit/compare/11.5.9...11.5.10
[11.5.9]: https://github.com/sebastianbergmann/phpunit/compare/11.5.8...11.5.9
[11.5.8]: https://github.com/sebastianbergmann/phpunit/compare/11.5.7...11.5.8
[11.5.7]: https://github.com/sebastianbergmann/phpunit/compare/11.5.6...11.5.7
[11.5.6]: https://github.com/sebastianbergmann/phpunit/compare/11.5.5...11.5.6
[11.5.5]: https://github.com/sebastianbergmann/phpunit/compare/11.5.4...11.5.5
[11.5.4]: https://github.com/sebastianbergmann/phpunit/compare/11.5.3...11.5.4
[11.5.3]: https://github.com/sebastianbergmann/phpunit/compare/11.5.2...11.5.3
[11.5.2]: https://github.com/sebastianbergmann/phpunit/compare/11.5.1...11.5.2
[11.5.1]: https://github.com/sebastianbergmann/phpunit/compare/11.5.0...11.5.1
[11.5.0]: https://github.com/sebastianbergmann/phpunit/compare/11.4.4...11.5.0
