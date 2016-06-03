# Changes in PHPUnit 5.3

All notable changes of the PHPUnit 5.3 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [5.3.5] - 2016-06-03

### Fixed

* Fixed [phpunit-mock-objects/#308](https://github.com/sebastianbergmann/phpunit-mock-objects/issues/308): Make sure that PHPUnit 5.3 does not pull in PHPUnit 5.4 dependencies

## [5.3.4] - 2016-05-11

### Changed

* The checks that can be enabled by `--strict-coverage` and `beStrictAboutCoversAnnotation` are no longer performed for tests annotated with `@medium` or `@large`

## [5.3.3] - 2016-05-10

### Fixed

* Fixed [#2158](https://github.com/sebastianbergmann/phpunit/issues/2158): Failure to run tests in separate processes if a file included into main process contains constant definition

## [5.3.2] - 2016-04-12

### Fixed

* Fixed [#2134](https://github.com/sebastianbergmann/phpunit/issues/2134): Failures are not shown when there are warnings
* Fixed [phpunit-mock-objects/#301](https://github.com/sebastianbergmann/phpunit-mock-objects/issues/301): `PHPUnit_Framework_MockObject_MockBuilder::getMock()` calls `PHPUnit_Framework_TestCase::getMock()` with more arguments than accepted

## [5.3.1] - 2016-04-07

### Fixed

* Fixed [#2128](https://github.com/sebastianbergmann/phpunit/issues/2128): PHPUnit 5.3 50% slower than PHPUnit 5.2 (when using large data sets with `@dataProvider`)

## [5.3.0] - 2016-04-01

### Added

* Implemented [#1984](https://github.com/sebastianbergmann/phpunit/issues/1984): Support for comparison operators to `@requires` annotation
* Added `--generate-configuration` option to generate an XML configuration file with suggested settings

### Changed

* In strict coverage mode, a test will now be marked as risky when it does not have a `@covers` annotation but is supposed to have one
* The passing of test doubles from one test to another has been improved
* Implemented [phpunit-mock-objects/#296](https://github.com/sebastianbergmann/phpunit-mock-objects/issues/296): Trigger an error when final or private method is configured on a test double

[5.3.5]: https://github.com/sebastianbergmann/phpunit/compare/5.3.4...5.3.5
[5.3.4]: https://github.com/sebastianbergmann/phpunit/compare/5.3.3...5.3.4
[5.3.3]: https://github.com/sebastianbergmann/phpunit/compare/5.3.2...5.3.3
[5.3.2]: https://github.com/sebastianbergmann/phpunit/compare/5.3.1...5.3.2
[5.3.1]: https://github.com/sebastianbergmann/phpunit/compare/5.3.0...5.3.1
[5.3.0]: https://github.com/sebastianbergmann/phpunit/compare/5.2...5.3.0

