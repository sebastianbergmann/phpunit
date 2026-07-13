# Changes in PHPUnit 13.3

All notable changes of the PHPUnit 13.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.3.0] - 2026-08-07

### Added

* [#3794](https://github.com/sebastianbergmann/phpunit/issues/3794): Filesystem-based code coverage targeting
* [#5758](https://github.com/sebastianbergmann/phpunit/issues/5758): Make export of objects customizable
* [#6586](https://github.com/sebastianbergmann/phpunit/pull/6586): Custom code coverage driver support
* [#6591](https://github.com/sebastianbergmann/phpunit/pull/6591): Repeated test execution using `--repeat` CLI option and `#[Repeat]` attribute
* [#6701](https://github.com/sebastianbergmann/phpunit/pull/6701): Allow `expectOutputString()` and `expectOutputRegex()` to be combined and repeated
* [#6710](https://github.com/sebastianbergmann/phpunit/pull/6710): Deprecation Filters
* [#6722](https://github.com/sebastianbergmann/phpunit/issues/6722): Allow `#[CoversNothing]` on methods
* [#6742](https://github.com/sebastianbergmann/phpunit/pull/6742): Retry failing tests up to N times using `--retry` CLI option `#[Retry]` attribute
* [#6827](https://github.com/sebastianbergmann/phpunit/pull/6827): Customize which deprecation trigger types fail the test run
* [#6830](https://github.com/sebastianbergmann/phpunit/issues/6830): Warn when `failOnAllIssues="true"` is combined with an explicitly disabled fine-grained `failOn*` setting
* [phpunit/php-code-coverage #1140](https://github.com/sebastianbergmann/php-code-coverage/pull/1140): Class-oriented HTML report
* [phpunit/php-code-coverage #1141](https://github.com/sebastianbergmann/php-code-coverage/pull/1141): Improve visualization of branch coverage and path coverage in the HTML report
* [phpunit/php-code-coverage #1153](https://github.com/sebastianbergmann/php-code-coverage/pull/1153): Filter HTML code coverage report by test size
* `--without-class-view` CLI option and `classView` attribute for the XML configuration file to disable the [class-oriented view](https://github.com/sebastianbergmann/php-code-coverage/pull/1140) in the HTML code coverage report
* `--without-file-view` CLI option and `fileView` attribute for the XML configuration file to disable the file-oriented view in the HTML code coverage report

### Changed

* [phpunit/php-code-coverage #1231](https://github.com/sebastianbergmann/php-code-coverage/pull/1231): Identify dead code using static analysis
* The test runner no longer crashes when an attribute cannot be instantiated
* Improved TestDox HTML report

[13.3.0]: https://github.com/sebastianbergmann/phpunit/compare/13.2...main
