# Changes in PHPUnit 8.2

All notable changes of the PHPUnit 8.2 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [8.2.1] - 2019-06-07

### Fixed

* Fixed [type#2](https://github.com/sebastianbergmann/type/issues/2): Stubbing of methods with `callable` or `iterable` return type does not work

## [8.2.0] - 2019-06-07

### Added

* Implemented [#3506](https://github.com/sebastianbergmann/phpunit/issues/3506): PHP options should be passed to child processes
* Implemented [#3586](https://github.com/sebastianbergmann/phpunit/issues/3586): Show time spent on code coverage report generation
* Implemented [#3682](https://github.com/sebastianbergmann/phpunit/issues/3682): Allow using `duration` for the `--order-by` option as well as for the `executionOrder` attribute in `phpunit.xml`

### Changed

* Implemented [#3122](https://github.com/sebastianbergmann/phpunit/issues/3122): Prevent runtime type error due to wrong return value configuration of test double
* Implemented [#3708](https://github.com/sebastianbergmann/phpunit/pull/3708): Built-in assertion and mock type definitions

### Fixed

* Fixed [#3602](https://github.com/sebastianbergmann/phpunit/issues/3602): PHPUnit silently ignores the return value on a `void` method of test double

[8.2.1]: https://github.com/sebastianbergmann/phpunit/compare/8.2.0...8.2.1
[8.2.0]: https://github.com/sebastianbergmann/phpunit/compare/8.1.6...8.2.0

