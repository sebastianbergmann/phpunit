# Changes in PHPUnit 13.4

All notable changes of the PHPUnit 13.4 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.4.0] - 2026-10-02

### Added

* [#6686](https://github.com/sebastianbergmann/phpunit/issues/6686): `Constraint::negatedToString()` and `Constraint::negatedFailureDescription()` for authoring the description of a constraint that is wrapped in `LogicalNot`
* [#6863](https://github.com/sebastianbergmann/phpunit/issues/6863): Cache which tests a test file contains
* `--coverage-jsonl` CLI option and `<jsonl>` element for the XML configuration file to write a code coverage report in JSONL format, one JSON object per line, that reports uncovered code rather than every executable line

### Deprecated

* [#6686](https://github.com/sebastianbergmann/phpunit/issues/6686): `Constraint::failureDescriptionInContext()` and `LogicalNot::negate()`

[13.4.0]: https://github.com/sebastianbergmann/phpunit/compare/13.3...main
