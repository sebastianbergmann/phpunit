# Changes in PHPUnit 13.4

All notable changes of the PHPUnit 13.4 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.4.0] - 2026-10-02

### Added

* [#6585](https://github.com/sebastianbergmann/phpunit/issues/6585): `executionOrder` attribute values and `--order-by` token lists that spell the order before `defects`, for example `duration-ascending,defects`
* [#6585](https://github.com/sebastianbergmann/phpunit/issues/6585): `pipeline()` on the `TestSuite\Sorted` event for inspecting which reordering stages were applied
* [#6686](https://github.com/sebastianbergmann/phpunit/issues/6686): `Constraint::negatedToString()` and `Constraint::negatedFailureDescription()` for authoring the description of a constraint that is wrapped in `LogicalNot`
* [#6863](https://github.com/sebastianbergmann/phpunit/issues/6863): Cache which tests a test file contains
* [#6957](https://github.com/sebastianbergmann/phpunit/issues/6957): Allow ordering tests by the time their source files were last modified
* `--coverage-jsonl` CLI option and `<jsonl>` element for the XML configuration file to write a code coverage report in JSONL format, one JSON object per line, that reports uncovered code rather than every executable line

### Deprecated

* [#6585](https://github.com/sebastianbergmann/phpunit/issues/6585): Writing `defects` before the order for `--order-by` and `executionOrder`, which will change meaning in PHPUnit 14
* [#6585](https://github.com/sebastianbergmann/phpunit/issues/6585): `depends` and `no-depends` for `--order-by` and `executionOrder`
* [#6585](https://github.com/sebastianbergmann/phpunit/issues/6585): Configuring more than one order for `--order-by` and `executionOrder`
* [#6585](https://github.com/sebastianbergmann/phpunit/issues/6585): Unknown values for the `executionOrder` XML configuration attribute, which are currently ignored
* [#6686](https://github.com/sebastianbergmann/phpunit/issues/6686): `Constraint::failureDescriptionInContext()` and `LogicalNot::negate()`

[13.4.0]: https://github.com/sebastianbergmann/phpunit/compare/13.3...main
