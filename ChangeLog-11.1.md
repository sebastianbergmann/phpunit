# Changes in PHPUnit 11.1

All notable changes of the PHPUnit 11.1 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [11.1.1] - 2024-04-06

### Fixed

* [#5798](https://github.com/sebastianbergmann/phpunit/issues/5798): The `#[CoversClass]` and `#[UsesClass]` attributes can no longer target traits

## [11.1.0] - 2024-04-05

### Added

* [#5689](https://github.com/sebastianbergmann/phpunit/issues/5689): Distinguish between self, direct and indirect deprecations
* [#5696](https://github.com/sebastianbergmann/phpunit/pull/5696): `#[DisableReturnValueGenerationForTestDoubles]` attribute for disabling return value generation for test doubles created using `createMock()`, `createMockForIntersectionOfInterfaces()`, `createPartialMock()`, `createStub()`, and `createStubForIntersectionOfInterfaces()`
* [#5175](https://github.com/sebastianbergmann/phpunit/issues/5175): `#[CoversMethod]` and `#[UsesMethod]` attributes for more fine-grained code coverage targeting
* [#5720](https://github.com/sebastianbergmann/phpunit/issues/5720): Support filtering using `--filter`, `--exclude-filter`, `--group`, and `--exclude-group` when listing tests using `--list-tests` and `--list-tests-xml` as well as listing groups with `--list-groups`
* [#5733](https://github.com/sebastianbergmann/phpunit/issues/5733): Implicitly include (abstract) parent class(es) with `#[CoversClass]` and `#[UsesClass]` attributes
* `--only-summary-for-coverage-text` CLI option to reduce the code coverage report in text format to a summary
* `--show-uncovered-for-coverage-text` CLI option to expand the code coverage report in text format to include a list of uncovered files

### Changed

* [#5689](https://github.com/sebastianbergmann/phpunit/issues/5689): The XML configuration file generated using `--generate-configuration` now generates `<source ignoreIndirectDeprecations="true" restrictNotices="true" restrictWarnings="true">` instead of `<source restrictDeprecations="true" restrictNotices="true" restrictWarnings="true">`
* [#5708](https://github.com/sebastianbergmann/phpunit/issues/5708): Allow the `--group`, `--exclude-group`, `--covers`, `--uses`, and `--test-suffix` CLI options to be used multiple times
* `PHPUnit\Framework\TestCase::__construct()` is now annotated to be final in preparation for declaring it `final` in PHPUnit 12
* Changed how the `DeprecationTriggered`, `ErrorTriggered`, `NoticeTriggered`, `PhpDeprecationTriggered`, `PhpNoticeTriggered`, `PhpWarningTriggered`, and `WarningTriggered` events are represented as text

### Deprecated

* [#5689](https://github.com/sebastianbergmann/phpunit/issues/5689): The `restrictDeprecations` attribute on the `<source>` element of the XML configuration file is now deprecated in favor of the `ignoreSelfDeprecations`, `ignoreDirectDeprecations`, and `ignoreIndirectDeprecations` attributes
* [#5709](https://github.com/sebastianbergmann/phpunit/issues/5709): Deprecate support for using comma-separated values with the `--group`, `--exclude-group`, `--covers`, `--uses`, and `--test-suffix` CLI options

[11.1.1]: https://github.com/sebastianbergmann/phpunit/compare/11.1.0...11.1.1
[11.1.0]: https://github.com/sebastianbergmann/phpunit/compare/11.0.10...11.1.0
