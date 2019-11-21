# Changes in PHPUnit 8.5

All notable changes of the PHPUnit 8.5 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [8.5.0] - 2019-12-06

### Added

* Implemented [#3911](https://github.com/sebastianbergmann/phpunit/issues/3911): Support combined use of `addMethods()` and `onlyMethods()`
* Implemented [#3949](https://github.com/sebastianbergmann/phpunit/issues/3949): Introduce specialized assertions `assertFileEqualsCanonicalizing()`, `assertFileEqualsIgnoringCase()`, `assertStringEqualsFileCanonicalizing()`, `assertStringEqualsFileIgnoringCase()`, `assertFileNotEqualsCanonicalizing()`, `assertFileNotEqualsIgnoringCase()`, `assertStringNotEqualsFileCanonicalizing()`, and `assertStringNotEqualsFileIgnoringCase()` as alternative to using `assertFileEquals()` etc. with optional parameters

[8.5.0]: https://github.com/sebastianbergmann/phpunit/compare/8.4...master

