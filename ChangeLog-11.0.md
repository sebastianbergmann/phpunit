# Changes in PHPUnit 11.0

All notable changes of the PHPUnit 11.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [11.0.9] - 2024-03-28

### Changed

* [#5766](https://github.com/sebastianbergmann/phpunit/pull/5766): Do not use a shell in `proc_open()` if not really needed
* [#5772](https://github.com/sebastianbergmann/phpunit/pull/5772): Cleanup process handling after dropping temp-file handling

### Fixed

* [#5570](https://github.com/sebastianbergmann/phpunit/pull/5570): Windows does not support exclusive locks on stdout

## [11.0.8] - 2024-03-22

### Fixed

* [#5765](https://github.com/sebastianbergmann/phpunit/pull/5765): Be more forgiving with error handlers that do not respect error suppression

## [11.0.7] - 2024-03-21

### Changed

* [#5747](https://github.com/sebastianbergmann/phpunit/pull/5747): Cache result of `Groups::groups()`
* [#5748](https://github.com/sebastianbergmann/phpunit/pull/5748): Improve performance of `NamePrettifier::prettifyTestMethodName()`
* [#5750](https://github.com/sebastianbergmann/phpunit/pull/5750): Micro-optimize `NamePrettifier::prettifyTestMethodName()` once again
* [#5752](https://github.com/sebastianbergmann/phpunit/issues/5752): Improve message for deprecation of doubling methods named "method"

### Fixed

* [#5760](https://github.com/sebastianbergmann/phpunit/issues/5760): TestDox printer does not display details about exceptions raised in before-test methods

## [11.0.6] - 2024-03-12

### Changed

* [#5727](https://github.com/sebastianbergmann/phpunit/pull/5727): Prevent duplicate call of `NamePrettifier::prettifyTestMethodName()`
* [#5739](https://github.com/sebastianbergmann/phpunit/pull/5739): Micro-optimize `NamePrettifier::prettifyTestMethodName()`
* [#5740](https://github.com/sebastianbergmann/phpunit/pull/5740): Micro-optimize `TestRunner::runTestWithTimeout()`
* [#5741](https://github.com/sebastianbergmann/phpunit/pull/5741): Save call to `Telemetry\System::snapshot()`
* [#5742](https://github.com/sebastianbergmann/phpunit/pull/5742): Prevent file IO when not strictly necessary
* [#5743](https://github.com/sebastianbergmann/phpunit/pull/5743): Prevent unnecessary `ExecutionOrderDependency::getTarget()` call
* [#5744](https://github.com/sebastianbergmann/phpunit/pull/5744): Simplify `NamePrettifier::prettifyTestMethodName()`

### Fixed

* [#5351](https://github.com/sebastianbergmann/phpunit/issues/5351): Incorrect code coverage metadata does not prevent code coverage data from being collected
* [#5729](https://github.com/sebastianbergmann/phpunit/pull/5729): `assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys()` does not correctly handle array order
* [#5746](https://github.com/sebastianbergmann/phpunit/issues/5746): Using `-d` CLI option multiple times triggers warning

## [11.0.5] - 2024-03-09

### Fixed

* [#5652](https://github.com/sebastianbergmann/phpunit/issues/5652): `HRTime::duration()` throws `InvalidArgumentException`
* `--exclude-filter` CLI option does not work

## [11.0.4] - 2024-02-29

### Fixed

* [#5704](https://github.com/sebastianbergmann/phpunit/issues/5704#issuecomment-1951105254): No warning when CLI options are used multiple times
* [#5707](https://github.com/sebastianbergmann/phpunit/issues/5707): `--fail-on-empty-test-suite` CLI option is not documented in `--help` output
* [#5716](https://github.com/sebastianbergmann/phpunit/pull/5716): Better respect PHP native array key handling for `assertArrayIs*ToArrayOnlyConsideringListOfKeys()`

## [11.0.3] - 2024-02-10

### Changed

* Tests that do not unregister their error handlers or exception handlers are no longer considered risky when they are run in an isolated process

### Fixed

* When a test (or the code called from it) does not unregister its own error handlers and its own exception handlers then only the latter was reported
* Resource usage information is printed when the `--debug` CLI option is used

## [11.0.2] - 2024-04-04

### Fixed

* [#5692](https://github.com/sebastianbergmann/phpunit/issues/5692): `--log-events-text` and `--log-events-verbose-text` require the destination file to exit

## [11.0.1] - 2024-02-02

### Fixed

* [#5690](https://github.com/sebastianbergmann/phpunit/issues/5690): Backward Compatibility break in `PHPUnit\Framework\Constraint\Constraint`

## [11.0.0] - 2024-02-02

### Added

* [#4964](https://github.com/sebastianbergmann/phpunit/pull/4964): Enable named data sets with the `#[TestWith*]` attributes
* [#5225](https://github.com/sebastianbergmann/phpunit/pull/5225): Allow providing named arguments from a data provider
* [#5600](https://github.com/sebastianbergmann/phpunit/pull/5600): Assertions for comparing arrays while ignoring a specified list of keys
* [#5605](https://github.com/sebastianbergmann/phpunit/pull/5605): `expectUserDeprecationMessage()` and `expectUserDeprecationMessageMatches()` for expecting `E_USER_DEPRECATED` issues
* [#5620](https://github.com/sebastianbergmann/phpunit/issues/5620): Implement `group` attribute on `<directory>` and `<file>` elements (children of `<testsuite>`) to add all tests found in a directory or file to a specified group
* [#5629](https://github.com/sebastianbergmann/phpunit/pull/5629): `--exclude-filter` CLI option for excluding tests from execution
* [#5642](https://github.com/sebastianbergmann/phpunit/pull/5642): `--list-test-files` CLI option to print the list of test files

### Changed

* [#5213](https://github.com/sebastianbergmann/phpunit/issues/5213): Make `TestCase` methods `protected` that should have been `protected` all along
* [#5254](https://github.com/sebastianbergmann/phpunit/issues/5254): Make `TestCase` methods `final` that should have been `final` all along
* [#5619](https://github.com/sebastianbergmann/phpunit/pull/5619): Check and restore error/exception global handlers
* The format of the XML document generated using the `--list-tests-xml` CLI option has been changed
* `small`, `medium`, and `large` can no longer be used as group names with the `#[Group]` attribute
* A test can no longer be part of multiple test suites that are configured in the XML configuration file
* `--check-version` now exits with a shell exit code that indicates failure when the version is not the latest version

### Deprecated

* [#4505](https://github.com/sebastianbergmann/phpunit/issues/4505): Support for metadata in doc-comments
* [#5214](https://github.com/sebastianbergmann/phpunit/issues/5214): `TestCase::iniSet()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5216](https://github.com/sebastianbergmann/phpunit/issues/5216): `TestCase::setLocale()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5240](https://github.com/sebastianbergmann/phpunit/issues/5240): `TestCase::createTestProxy()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5241](https://github.com/sebastianbergmann/phpunit/issues/5241): `TestCase::getMockForAbstractClass()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5242](https://github.com/sebastianbergmann/phpunit/issues/5242): `TestCase::getMockFromWsdl()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5243](https://github.com/sebastianbergmann/phpunit/issues/5243): `TestCase::getMockForTrait()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5244](https://github.com/sebastianbergmann/phpunit/issues/5244): `TestCase::getObjectForTrait()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5305](https://github.com/sebastianbergmann/phpunit/issues/5305): `MockBuilder::getMockForAbstractClass()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5306](https://github.com/sebastianbergmann/phpunit/issues/5306): `MockBuilder::getMockForTrait()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5307](https://github.com/sebastianbergmann/phpunit/issues/5307): `MockBuilder::enableProxyingToOriginalMethods()`, `MockBuilder::disableProxyingToOriginalMethods()`, and `MockBuilder::setProxyTarget()` (these methods were already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5308](https://github.com/sebastianbergmann/phpunit/issues/5308): `MockBuilder::allowMockingUnknownTypes()` and `MockBuilder::disallowMockingUnknownTypes()` (these methods were already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5309](https://github.com/sebastianbergmann/phpunit/issues/5309): `MockBuilder::enableAutoload()` and `MockBuilder::disableAutoload()` (these methods were already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5315](https://github.com/sebastianbergmann/phpunit/issues/5315): `MockBuilder::enableArgumentCloning()` and `MockBuilder::disableArgumentCloning()` (these methods were already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5320](https://github.com/sebastianbergmann/phpunit/issues/5320): `MockBuilder::addMethods()` (this method was already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5415](https://github.com/sebastianbergmann/phpunit/issues/5415): Support for doubling interfaces (or classes) that have a method named `method`
* [#5423](https://github.com/sebastianbergmann/phpunit/issues/5423): `TestCase::returnValue()`, `TestCase::onConsecutiveCalls()`, `TestCase::returnValueMap()`, `TestCase::returnArgument()`, `TestCase::returnSelf()`, and `TestCase::returnCallback()` (these methods were already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5472](https://github.com/sebastianbergmann/phpunit/issues/5472): `assertStringNotMatchesFormat()` and `assertStringNotMatchesFormatFile()` (these methods were already [soft-deprecated](https://phpunit.de/backward-compatibility.html#soft-deprecation) in PHPUnit 10)
* [#5535](https://github.com/sebastianbergmann/phpunit/issues/5535): Configuring expectations using `expects()` on test stubs

### Removed

* [#4600](https://github.com/sebastianbergmann/phpunit/issues/4600): Support for old cache configuration
* [#4604](https://github.com/sebastianbergmann/phpunit/issues/4604): Support for `backupStaticAttributes` attribute in XML configuration file
* [#4779](https://github.com/sebastianbergmann/phpunit/issues/4779): Support for `forceCoversAnnotation` and `beStrictAboutCoversAnnotation` attributes in XML configuration file
* [#5100](https://github.com/sebastianbergmann/phpunit/issues/5100): Support for non-static data provider methods, non-public data provider methods, and data provider methods that declare parameters
* [#5101](https://github.com/sebastianbergmann/phpunit/issues/5101): Support for PHP 8.1
* [#5272](https://github.com/sebastianbergmann/phpunit/issues/5272): Optional parameters of `PHPUnit\Framework\Constraint\IsEqual::__construct()`
* [#5329](https://github.com/sebastianbergmann/phpunit/issues/5329): Support for configuring include/exclude list for code coverage using the `<coverage>` element
* [#5482](https://github.com/sebastianbergmann/phpunit/issues/5482): `dataSet` attribute for `testCaseMethod` elements in the XML document generated by `--list-tests-xml`
* [#5514](https://github.com/sebastianbergmann/phpunit/issues/5514): `IgnoreClassForCodeCoverage`, `IgnoreMethodForCodeCoverage`, and `IgnoreFunctionForCodeCoverage` attributes
* [#5604](https://5604github.com/sebastianbergmann/phpunit/pull/5604): `Test\AssertionFailed` and `Test\AssertionSucceeded` events
* `registerMockObjectsFromTestArgumentsRecursively` attribute on the `<phpunit>` element of the XML configuration file
* `CodeCoverageIgnore` attribute
* `PHPUnit\TextUI\Configuration\Configuration::coverageExcludeDirectories()` (use `PHPUnit\TextUI\Configuration\Configuration::source()->excludeDirectories()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::coverageExcludeFiles()` (use `PHPUnit\TextUI\Configuration\Configuration::source()->excludeFiles()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::coverageIncludeDirectories()` (use `PHPUnit\TextUI\Configuration\Configuration::source()->includeDirectories()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::coverageIncludeFiles()` (use `PHPUnit\TextUI\Configuration\Configuration::source()->includeFiles()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::loadPharExtensions()` (use `PHPUnit\TextUI\Configuration\Configuration::noExtensions()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport()` (use `PHPUnit\TextUI\Configuration\Configuration::source()->notEmpty()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::restrictDeprecations()` (use `PHPUnit\TextUI\Configuration\Configuration::source()->restrictDeprecations()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::restrictNotices()` (use `PHPUnit\TextUI\Configuration\Configuration::source()->restrictNotices()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::restrictWarnings()` (use `PHPUnit\TextUI\Configuration\Configuration::source()->restrictWarnings()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::cliArgument()` (use `PHPUnit\TextUI\Configuration\Configuration::cliArguments()[0]` instead)
* `PHPUnit\TextUI\Configuration\Configuration::hasCliArgument()` (use `PHPUnit\TextUI\Configuration\Configuration::hasCliArguments()` instead)
* `PHPUnit\TextUI\Configuration\Configuration::registerMockObjectsFromTestArgumentsRecursively()`
* `PHPUnit\Framework\Constraint\Constraint::exporter()`

[11.0.9]: https://github.com/sebastianbergmann/phpunit/compare/11.0.8...11.0.9
[11.0.8]: https://github.com/sebastianbergmann/phpunit/compare/11.0.7...11.0.8
[11.0.7]: https://github.com/sebastianbergmann/phpunit/compare/11.0.6...11.0.7
[11.0.6]: https://github.com/sebastianbergmann/phpunit/compare/11.0.5...11.0.6
[11.0.5]: https://github.com/sebastianbergmann/phpunit/compare/11.0.4...11.0.5
[11.0.4]: https://github.com/sebastianbergmann/phpunit/compare/11.0.3...11.0.4
[11.0.3]: https://github.com/sebastianbergmann/phpunit/compare/11.0.2...11.0.3
[11.0.2]: https://github.com/sebastianbergmann/phpunit/compare/11.0.1...11.0.2
[11.0.1]: https://github.com/sebastianbergmann/phpunit/compare/11.0.0...11.0.1
[11.0.0]: https://github.com/sebastianbergmann/phpunit/compare/10.5...11.0.0
