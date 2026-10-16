# Changes in PHPUnit 13.4

All notable changes of the PHPUnit 13.4 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.4.3] - 2026-10-16

### Fixed

* The dependencies of a test that are listed after a dependency on a test that does not return a value, or after a dependency on a larger test, were not checked, so the test was run instead of being skipped when one of these dependencies had not passed
* Tests could not be run in process isolation when the namespace of the test class begins with a name that the code of the child process imports, for instance `CodeCoverage` or `Facade`
* A test that is run in process isolation aborts the test run when its data set or a value returned by a test it depends on cannot be serialized, for instance because it is a closure
* When a before-first-test method or an after-last-test method errors or fails, the remaining methods of the same kind are still called, and each of them that does not error or fail itself is reported with the exception of the earlier method
* When a hook method of a test, for instance a `#[Before]` method, skips the test, the remaining hook methods of the same kind are still called, so the test is reported as errored instead of skipped when one of them errors
* An error or failure in a before-first-test method or an after-last-test method that is called again in the child process of a test that is run in process isolation is reported twice, once for the test class and once for the test
* When a before-first-test method errors, the progress output shows one `E` for the test class instead of one for each of its tests, only one of its tests is counted as run, and the test that is run next is shown as an error
* When a before-first-test method fails, the progress output shows nothing for the tests of the test class and only one of them is counted as run
* The Open Test Reporting XML logfile has no start for a test that errors before it is prepared, for instance because it depends on a test that does not exist, and no end for the test that is run after a test that is skipped because a test it depends on did not pass or because one of its
  requirements is not met
* The Open Test Reporting XML logfile has no entry for a test that is marked as incomplete before it is prepared, for instance in `setUp()`, and reports the test that is run after it as aborted with the message of the incomplete test
* The Open Test Reporting XML logfile has no end for the test that is run after a test whose preparation errors, for instance because `setUp()` raises an exception or the global state cannot be backed up
* The Open Test Reporting XML logfile reports a test that has more than one outcome, for instance because `tearDown()` fails after the test errored, with its last outcome instead of its most severe one, and with more than one end when the preparation of the test errored
* The Open Test Reporting XML logfile is not well-formed when a test name, a message, the output of a test, or the string representation of an exception contains `]]>`, bytes that are not valid UTF-8, or characters that are not allowed in XML, for instance the escape character of an ANSI
  escape sequence
* The Open Test Reporting XML logfile reports an issue that is triggered before a test is prepared, for instance in `setUp()`, with an empty ID before the start of the test

## [13.4.2] - 2026-10-12

### Fixed

* [#7033](https://github.com/sebastianbergmann/phpunit/pull/7033): Compact printer is created although an extension replaces the progress output
* [#7035](https://github.com/sebastianbergmann/phpunit/pull/7035): Sorting tests by duration recomputes the duration of a test suite for every comparison
* Sorting tests by size recomputes the size of a test suite for every comparison
* `expectUserDeprecationMessage()` and `expectUserDeprecationMessageMatches()` did not see the deprecations triggered by an attempt of a retried test and saw those of the previously run test instead
* The status of a test whose mock object raises an exception in its destructor is success although the test is reported as errored, so `#[Retry]` does not retry the test, `--repeat` does not skip the remaining repetitions, `tearDown()` sees the wrong status, and code coverage records the test as successful
* Named arguments passed to `with()` were matched by position instead of by name, so an expectation such as `->with(b: 7)` compared the expected value with the first parameter of the method and the parameter named `$b` was not checked
* String keys of the parameter sets passed to `withParameterSetsInOrder()`, `withParameterSetsInAnyOrder()`, and `withParameterSetsInPartialOrder()` were ignored, so a parameter set such as `['b' => 7]` compared the expected value with the first parameter of the method and the parameter
  named `$b` was not checked

## [13.4.1] - 2026-10-05

### Changed

* The Software Bill of Materials (SBOM) embedded in the PHAR now uses CycloneDX 1.7 and provides the NTIA minimum elements as well as the data fields that BSI TR-03183-2 (version 2.1.0) requires for logical and identified components, including component creators, original, distribution and effective licences, source code URIs, and the PHP runtime and its extensions as external components

### Fixed

* [#7029](https://github.com/sebastianbergmann/phpunit/issues/7029): `TestSuite::runTests()` doubles garbage collector time when `zend.exception_ignore_args` is off
* `setUpBeforeClass()` and `tearDownAfterClass()` were run twice for a test class that is the only test class in a test suite of the XML configuration file that has the name of that test class
* Tests could not be run in process isolation when the path of the bootstrap script, the test file, or the directory for temporary files contained a single quote
* Arguments in the `--ARGS--` section of a PHPT test that start with `-` were interpreted by PHP instead of being passed to the test when the test also has a `--STDIN--` section
* Recording the tests that passed in a child process copied all tests that had passed so far, which made running many tests in process isolation increasingly slow

## [13.4.0] - 2026-10-02

### Added

* [#6585](https://github.com/sebastianbergmann/phpunit/issues/6585): `executionOrder` attribute values and `--order-by` token lists that spell the order before `defects`, for example `duration-ascending,defects`
* [#6585](https://github.com/sebastianbergmann/phpunit/issues/6585): `pipeline()` on the `TestSuite\Sorted` event for inspecting which reordering stages were applied
* [#6686](https://github.com/sebastianbergmann/phpunit/issues/6686): `Constraint::negatedToString()` and `Constraint::negatedFailureDescription()` for authoring the description of a constraint that is wrapped in `LogicalNot`
* [#6863](https://github.com/sebastianbergmann/phpunit/issues/6863): Cache which tests a test file contains
* [#6957](https://github.com/sebastianbergmann/phpunit/issues/6957): Allow ordering tests by the time their source files were last modified
* [#6958](https://github.com/sebastianbergmann/phpunit/issues/6958): Select the tests that are in all of several groups
* [#6960](https://github.com/sebastianbergmann/phpunit/pull/6960): Record the order in which methods of mock objects are invoked
* [#6964](https://github.com/sebastianbergmann/phpunit/pull/6964): `#[RequiresClass]` attribute to skip a test if a class does not exist
* [#6995](https://github.com/sebastianbergmann/phpunit/issues/6995): `ChildProcessExtension` interface for extensions that need to be bootstrapped in the child process of a test that runs in process isolation
* `--coverage-jsonl` CLI option and `<jsonl>` element for the XML configuration file to write a code coverage report in JSONL format, one JSON object per line, that reports uncovered code rather than every executable line
* `--timeout` CLI option to limit the wall-clock time of the entire test run
* `requireCoverageMetadataOnSmallTests`, `requireCoverageMetadataOnMediumTests`, and `requireCoverageMetadataOnLargeTests` attributes for the XML configuration file to require code coverage metadata depending on the size of a test; these have precedence over `requireCoverageMetadata`

### Changed

* Output printed by a test is now shown in the compact output as a `--- OUTPUT:` record that is attributed to the test instead of being passed through unformatted; the record is omitted when `beStrictAboutOutputDuringTests` or `--disallow-test-output` already reports the output as risky

### Deprecated

* [#6585](https://github.com/sebastianbergmann/phpunit/issues/6585): Writing `defects` before the order for `--order-by` and `executionOrder`, which will change meaning in PHPUnit 14
* [#6585](https://github.com/sebastianbergmann/phpunit/issues/6585): `depends` and `no-depends` for `--order-by` and `executionOrder`
* [#6585](https://github.com/sebastianbergmann/phpunit/issues/6585): Configuring more than one order for `--order-by` and `executionOrder`
* [#6585](https://github.com/sebastianbergmann/phpunit/issues/6585): Unknown values for the `executionOrder` XML configuration attribute, which are currently ignored
* [#6686](https://github.com/sebastianbergmann/phpunit/issues/6686): `Constraint::failureDescriptionInContext()` and `LogicalNot::negate()`
* [#6999](https://github.com/sebastianbergmann/phpunit/issues/6999): Class-level `#[Group]`, `#[Ticket]`, `#[Small]`, `#[Medium]`, and `#[Large]` on parent classes of test classes

### Fixed

* Issues that are listed in the baseline are not ignored for tests that are run in a separate process
* A test that is run in process isolation aborts the test run when its result cannot be unserialized, for instance because it returns an object that holds a test double
* A test that uses `#[DataProviderClosure]` errors when it is run in process isolation

[13.4.3]: https://github.com/sebastianbergmann/phpunit/compare/13.4.2...13.4.3
[13.4.2]: https://github.com/sebastianbergmann/phpunit/compare/13.4.1...13.4.2
[13.4.1]: https://github.com/sebastianbergmann/phpunit/compare/13.4.0...13.4.1
[13.4.0]: https://github.com/sebastianbergmann/phpunit/compare/13.3.6...13.4.0
