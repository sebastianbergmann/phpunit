# Changes in PHPUnit 13.3

All notable changes of the PHPUnit 13.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.3.1] - 2026-08-13

### Changed

* Invoking a static hook method such as `setUpBeforeClass()` no longer triggers a deprecation warning on PHP 8.6

## [13.3.0] - 2026-08-07

### Added

* [#3794](https://github.com/sebastianbergmann/phpunit/issues/3794): Filesystem-based code coverage targeting
* [#5758](https://github.com/sebastianbergmann/phpunit/issues/5758): Make export of objects customizable
* [#6546](https://github.com/sebastianbergmann/phpunit/issues/6546): Both property hooks can now be configured on test doubles of virtual hooked properties, even when the doubled property only declares one of them
* [#6586](https://github.com/sebastianbergmann/phpunit/pull/6586): Custom code coverage driver support
* [#6591](https://github.com/sebastianbergmann/phpunit/pull/6591): Repeated test execution using `--repeat` CLI option and `#[Repeat]` attribute
* [#6701](https://github.com/sebastianbergmann/phpunit/pull/6701): Allow `expectOutputString()` and `expectOutputRegex()` to be combined and repeated
* [#6710](https://github.com/sebastianbergmann/phpunit/pull/6710): Deprecation Filters
* [#6722](https://github.com/sebastianbergmann/phpunit/issues/6722): Allow `#[CoversNothing]` on methods
* [#6742](https://github.com/sebastianbergmann/phpunit/pull/6742): Retry failing tests up to N times using `--retry` CLI option `#[Retry]` attribute
* [#6827](https://github.com/sebastianbergmann/phpunit/pull/6827): Customize which deprecation trigger types fail the test run
* [#6830](https://github.com/sebastianbergmann/phpunit/issues/6830): Warn when `failOnAllIssues="true"` is combined with an explicitly disabled fine-grained `failOn*` setting
* [#6832](https://github.com/sebastianbergmann/phpunit/pull/6832): Allow doubling properties that do not declare property hooks
* [#6853](https://github.com/sebastianbergmann/phpunit/issues/6853): Optionally warn when PHP is not configured for development
* [phpunit/php-code-coverage #1140](https://github.com/sebastianbergmann/php-code-coverage/pull/1140): Class-oriented HTML report
* [phpunit/php-code-coverage #1141](https://github.com/sebastianbergmann/php-code-coverage/pull/1141): Improve visualization of branch coverage and path coverage in the HTML report
* [phpunit/php-code-coverage #1153](https://github.com/sebastianbergmann/php-code-coverage/pull/1153): Filter HTML code coverage report by test size
* `--record-test-run-history` and `--do-not-record-test-run-history` CLI options as well as the `recordTestRunHistory` attribute for the XML configuration file to control whether the status and duration of each test are recorded for use by `--order-by defects` and `--order-by duration-*`
* `--without-class-view` CLI option and `classView` attribute for the XML configuration file to disable the [class-oriented view](https://github.com/sebastianbergmann/php-code-coverage/pull/1140) in the HTML code coverage report
* `--without-file-view` CLI option and `fileView` attribute for the XML configuration file to disable the file-oriented view in the HTML code coverage report
* `{PWD}` is now substituted with the directory of the PHPT test file in `--ENV--` and `--INI--` sections of PHPT test files
* `{TMP}` (system directory for temporary files) and `{ENV:name}` (value of environment variable `name`) are now substituted in `--INI--` sections of PHPT test files
* A PHPT test whose `--INI--` section references an environment variable that is not set is now skipped
* A `--SKIPIF--` section of a PHPT test file that prints `xfail <reason>` now marks the test as expected to fail, as if the PHPT test file had an `--XFAIL--` section with that reason

### Changed

* [phpunit/php-code-coverage #1231](https://github.com/sebastianbergmann/php-code-coverage/pull/1231): Identify dead code using static analysis
* [phpunit/php-code-coverage #1259](https://github.com/sebastianbergmann/php-code-coverage/pull/1259): Degrade gracefully when a source file cannot be parsed
* The test runner no longer crashes when an attribute cannot be instantiated
* Improved TestDox HTML report
* The feature formerly named "test result cache" is now named "test run history"; when a cache directory is configured, the file it is stored in is now named `test-run-history` instead of `test-results`
* The test runner warns now when ordering by defects or duration is configured but recording of the test run history is disabled
* `TestCase` no longer captures `error_log()` output for tests that do not use `expectErrorLog()`, avoiding the cost of setting up error log redirection for every test
* `error_log()` output from tests without an expectation is no longer echoed (date-stripped) to PHPUnit's output; it goes to the configured error log again, as it did before capture was introduced
* A test running in process isolation that calls `error_log()` without `expectErrorLog()` now produces stderr output in the child process, which the test runner reports as a test error
* A PHPT test that is expected to fail (`--XFAIL--` section or `xfail` output from the `--SKIPIF--` section) but passes is now considered risky; this usually means the expected-failure marker is stale and should be removed
* A PHPT test whose `--SKIPIF--` section produces output that is not recognized is now considered risky; this usually means the skip check itself is broken. The keywords understood by PHP's own test runner that have no PHPUnit counterpart (`info`, `warn`, `xleak`, `flaky`, and `nocache`) are tolerated and do not make the test risky
* PHPT tests now run with additional INI defaults for deterministic output (`date.timezone=UTC`, `display_startup_errors=1`, `fatal_error_backtraces=Off`, `ignore_repeated_errors=0`, `precision=14`,
  `serialize_precision=-1`), consistent with PHP's own test runner; all of them can be overridden per test using the `--INI--` section

### Deprecated

* `--cache-result` CLI option, use `--record-test-run-history` instead
* `--do-not-cache-result` CLI option, use `--do-not-record-test-run-history` instead
* `cacheResult` XML configuration attribute, use `recordTestRunHistory` instead
* `PHPUnit\TextUI\Configuration\Configuration::cacheResult()`, use `PHPUnit\TextUI\Configuration\Configuration::recordTestRunHistory()` instead
* `PHPUnit\TextUI\Configuration\Configuration::testResultCacheFile()`, use `PHPUnit\TextUI\Configuration\Configuration::testRunHistoryFile()` instead

### Fixed

* Doubling a class with a property that declares both a final and a non-final hook no longer triggers a fatal error
* `expectErrorLog()` now only considers `error_log()` output written after it was called; previously, the expectation was also satisfied by output written before `expectErrorLog()` was called
* PHPT test files with an unknown section, a duplicated section, more than one of the `--FILE--`, `--FILEEOF--`, and `--FILE_EXTERNAL--` sections, or more than one expectation section (`--EXPECT--`, `--EXPECTF--`, `--EXPECTREGEX--`, and their `_EXTERNAL` variants) are now rejected; previously, misspelled sections were silently ignored and duplicated sections silently overwrote each other
* The regular expression from an `--EXPECTREGEX--` section must now match the PHPT test's entire output, and `.` now matches newline characters, consistent with PHP's own test runner; previously, a match on a substring of the output was sufficient for the test to pass
* The reason printed by a `--SKIPIF--` section of a PHPT test is no longer mangled when it follows the `skip <reason>` convention used by PHP's own test suite; previously, the first two characters of the reason were stripped unless the `skip: <reason>` or `skip - <reason>` convention was used
* The test runner no longer aborts with an uncaught `PHPUnit\Runner\Phpt\InvalidPhptFileException` when a PHPT test file has an empty `--FILE--` or `--FILEEOF--` section or a `--FILE_EXTERNAL--` section that references an empty file; such a file is now rejected while it is parsed and reported as an errored test
* `PHPUnit\Runner\Phpt\InvalidPhptFileException` now has a message that explains why the PHPT test file was rejected

[13.3.1]: https://github.com/sebastianbergmann/phpunit/compare/13.3.0...13.3.1
[13.3.0]: https://github.com/sebastianbergmann/phpunit/compare/13.2.6...13.3.0
