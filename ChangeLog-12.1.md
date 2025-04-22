# Changes in PHPUnit 12.1

All notable changes of the PHPUnit 12.1 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [12.1.3] - 2025-04-22

### Changed

* When gathering the telemetry information that each event has, the real size of memory allocated from the operating system is no longer used as this is grown by PHP's memory manager in chunks that are so large that small(er) increases in peak memory usage cannot be seen
* The peak memory usage returned by `memory_get_peak_usage()` is now reset immediately before the `Test\Prepared` event is emitted using `memory_reset_peak_usage()` so that (memory usage at `Test\Finished` - memory usage at `Test\Prepared`) is a better approximation of the memory usage of the test
* The string representation of `Telemetry\Info` now uses peak memory usage instead of memory usage (this affects `--log-events-verbose-text`)

### Fixed

* [#6173](https://github.com/sebastianbergmann/phpunit/issues/6173): Output from `error_log()` is not displayed when test fails
* A "Before Test Method Errored" event is no longer emitted when a test is skipped in a "before test" method

## [12.1.2] - 2025-04-08

### Fixed

* [#6104](https://github.com/sebastianbergmann/phpunit/issues/6104): Reverted change introduced in PHPUnit 12.1.1

## [12.1.1] - 2025-04-08

### Fixed

* [#6104](https://github.com/sebastianbergmann/phpunit/issues/6104): Test with dependencies and data provider fails
* [#6174](https://github.com/sebastianbergmann/phpunit/issues/6174): `willReturnMap()` fails with nullable parameters when their default is `null` and no argument is passed for them

## [12.1.0] - 2025-04-04

### Added

* [#6118](https://github.com/sebastianbergmann/phpunit/pull/6118): `expectErrorLog()` for expecting `error_log()` output
* [#6126](https://github.com/sebastianbergmann/phpunit/pull/6126): Attribute `#[WithEnvironmentVariable]` for setting an environment variable for the duration of a test
* The `AfterTestMethodCalled`, `AfterTestMethodErrored`, `AfterTestMethodFinished`, `BeforeTestMethodCalled`, `BeforeTestMethodErrored`, `BeforeTestMethodFinished`, `PostConditionCalled`, `PostConditionErrored`, `PostConditionFinished`, `PreConditionCalled`, `PreConditionErrored`, and `PreConditionFinished` event value objects now have `test()` method that returns a value object representing the test method for which the hook method was called

### Changed

* When code coverage processing is requested and no static analysis cache directory has been configured then a cache directory in the operating system's path used for temporary files is automatically created and used
* The static analysis of first-party source files required for the code coverage functionality is now performed before the first test is run, if code coverage processing is requested (via the XML configuration file and/or CLI options), all first-party source files are configured to be processed (which is the default), and a static analysis cache directory is available (either explicitly configured or automatically determined, see above). This has the same effect as running `phpunit --warm-coverage-cache` before running tests.

### Deprecated

* [#6140](https://github.com/sebastianbergmann/phpunit/issues/6140): The `testClassName()` method on the `AfterTestMethodCalled`, `AfterTestMethodErrored`, `AfterTestMethodFinished`, `BeforeTestMethodCalled`, `BeforeTestMethodErrored`, `BeforeTestMethodFinished`, `PostConditionCalled`, `PostConditionErrored`, `PostConditionFinished`, `PreConditionCalled`, `PreConditionErrored`, and `PreConditionFinished` event value objects (use `test()->className()` instead)

[12.1.3]: https://github.com/sebastianbergmann/phpunit/compare/12.1.2...12.1.3
[12.1.2]: https://github.com/sebastianbergmann/phpunit/compare/12.1.1...12.1.2
[12.1.1]: https://github.com/sebastianbergmann/phpunit/compare/12.1.0...12.1.1
[12.1.0]: https://github.com/sebastianbergmann/phpunit/compare/12.0.10...12.1.0
