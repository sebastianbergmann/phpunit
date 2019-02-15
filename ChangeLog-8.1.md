# Changes in PHPUnit 8.1

All notable changes of the PHPUnit 8.1 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [8.1.0] - 2019-04-05

### Added

* Implemented [#3528](https://github.com/sebastianbergmann/phpunit/pull/3528): Option to disable TestDox progress animation
* Information on test groups in the TestDox XML report is now reported in `group` elements that are child nodes of `test`
* Information from `@covers` and `@uses` annotations is now reported in TestDox XML

### Changed

* The `groups` attribute on the `test` element in the TestDox XML report is now deprecated

[8.1.0]: https://github.com/sebastianbergmann/phpunit/compare/8.0...8.1.0

