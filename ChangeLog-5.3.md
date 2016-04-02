# Changes in PHPUnit 5.3

All notable changes of the PHPUnit 5.3 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [5.3.0] - 2016-04-01

### Added

* Implemented [#1984](https://github.com/sebastianbergmann/phpunit/issues/1984): Support for comparison operators to `@requires` annotation
* Added `--generate-configuration` option to generate an XML configuration file with suggested settings

### Changed

* In strict coverage mode, a test will now be marked as risky when it does not have a `@covers` annotation but is supposed to have one
* The passing of test doubles from one test to another has been improved
* Implemented [phpunit-mock-objects/#296](https://github.com/sebastianbergmann/phpunit-mock-objects/issues/296): Trigger an error when final or private method is configured on a test double

[5.3.0]: https://github.com/sebastianbergmann/phpunit/compare/5.2...5.3.0

