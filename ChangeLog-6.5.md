# Changes in PHPUnit 6.5

All notable changes of the PHPUnit 6.5 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [6.5.5] - 2017-12-17

### Fixed

* Fixed [#2922](https://github.com/sebastianbergmann/phpunit/issues/2922): Test class is not discovered when there is a test class with `@group` and provider throwing exception in it, tests are run with `--exclude-group` for that group, there is another class called later (after the class from above), and the name of that another class does not match its filename

## [6.5.4] - 2017-12-10

### Changed

* Require version 5.0.5 of `phpunit/phpunit-mock-objects` for [phpunit-mock-objects#394](https://github.com/sebastianbergmann/phpunit-mock-objects/issues/394)

## [6.5.3] - 2017-12-06

### Fixed

* Fixed an issue with PHPT tests when `forceCoversAnnotation="true"` is configured

## [6.5.2] - 2017-12-02

### Changed

* Require version 5.0.4 of `phpunit/phpunit-mock-objects` for [phpunit-mock-objects#388](https://github.com/sebastianbergmann/phpunit-mock-objects/issues/388)

## [6.5.1] - 2017-12-01

* Fixed [#2886](https://github.com/sebastianbergmann/phpunit/pull/2886): Forced environment variables do not affect `getenv()`

## [6.5.0] - 2017-12-01

### Added

* Implemented [#2286](https://github.com/sebastianbergmann/phpunit/issues/2286): Optional `$exit` parameter for `PHPUnit\TextUI\TestRunner::run()`
* Implemented [#2496](https://github.com/sebastianbergmann/phpunit/issues/2496): Allow shallow copy of dependencies

### Fixed

* Fixed [#2654](https://github.com/sebastianbergmann/phpunit/issues/2654): Problems with `assertJsonStringEqualsJsonString()`
* Fixed [#2810](https://github.com/sebastianbergmann/phpunit/pull/2810): Code Coverage for PHPT tests does not work

[6.5.5]: https://github.com/sebastianbergmann/phpunit/compare/6.5.4...6.5.5
[6.5.4]: https://github.com/sebastianbergmann/phpunit/compare/6.5.3...6.5.4
[6.5.3]: https://github.com/sebastianbergmann/phpunit/compare/6.5.2...6.5.3
[6.5.2]: https://github.com/sebastianbergmann/phpunit/compare/6.5.1...6.5.2
[6.5.1]: https://github.com/sebastianbergmann/phpunit/compare/6.5.0...6.5.1
[6.5.0]: https://github.com/sebastianbergmann/phpunit/compare/6.4...6.5.0

