# Changes in PHPUnit 4.5

All notable changes of the PHPUnit 4.5 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [4.5.1] - 2015-03-29

## [4.5.0] - 2015-02-05

### Added

* Added out-of-the-box support for [Prophecy](https://github.com/phpspec/prophecy)
* Implemented [#137](https://github.com/sebastianbergmann/phpunit/issues/137): Add support for variable number of tests shown per line in default result printer

### Changed

* Merged [#1478](https://github.com/sebastianbergmann/phpunit/issues/1478): Improve the performance of `PHPUnit_Framework_Constraint_IsEqual` (which is used by `assertEquals()`, for instance) for the most common case

### Deprecated

* [Deprecated](https://github.com/sebastianbergmann/phpunit/commit/7abe7796f77b13fdf3cfc506fb987d6c2ab477f5) the `--strict` commandline option and the XML configuration's `strict` attribute

### Fixed

* Fixed [#1474](https://github.com/sebastianbergmann/phpunit/issues/1474): Allow the registration of custom comparators for `assertEquals()` et al. (again)

[4.5.1]: https://github.com/sebastianbergmann/phpunit/compare/4.5.0...4.5.1
[4.5.0]: https://github.com/sebastianbergmann/phpunit/compare/4.4...4.5.0

