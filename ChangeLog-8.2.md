# Changes in PHPUnit 8.2

All notable changes of the PHPUnit 8.2 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [8.2.5] - 2019-07-15

### Fixed

* Fixed [#3747](https://github.com/sebastianbergmann/phpunit/pull/3747): Regression in `StringStartsWith` constraint
* Fixed [#3752](https://github.com/sebastianbergmann/phpunit/issues/3752): `expectException()` fails with `getMockForAbstractClass()`

## [8.2.4] - 2019-07-03

### Changed

* Implemented [#3744](https://github.com/sebastianbergmann/phpunit/pull/3744): More context when value with incompatible type is configured to be returned by stub

## [8.2.3] - 2019-06-19

### Fixed

* Fixed [#3722](https://github.com/sebastianbergmann/phpunit/issues/3722): `getObjectForTrait()` does not work for traits that declare a constructor
* Fixed [#3723](https://github.com/sebastianbergmann/phpunit/pull/3723): Unescaped dash in character group in regular expression

## [8.2.2] - 2019-06-15

### Changed

* Scoped PHAR built with newer version of PHP-Scoper

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

[8.2.5]: https://github.com/sebastianbergmann/phpunit/compare/8.2.4...8.2.5
[8.2.4]: https://github.com/sebastianbergmann/phpunit/compare/8.2.3...8.2.4
[8.2.3]: https://github.com/sebastianbergmann/phpunit/compare/8.2.2...8.2.3
[8.2.2]: https://github.com/sebastianbergmann/phpunit/compare/8.2.1...8.2.2
[8.2.1]: https://github.com/sebastianbergmann/phpunit/compare/8.2.0...8.2.1
[8.2.0]: https://github.com/sebastianbergmann/phpunit/compare/8.1.6...8.2.0

