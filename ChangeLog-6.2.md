# Changes in PHPUnit 6.2

All notable changes of the PHPUnit 6.2 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [6.2.2] - 2017-06-13

### Fixed

* Fixed [#2703](https://github.com/sebastianbergmann/phpunit/pull/2703): `RiskyTestError` class is not declared in the correct namespace

## [6.2.1] - 2017-06-02

### Changed

* Rolled back the change that when `beStrictAboutCoversAnnotation="true"` is configured or `--strict-coverage` is used then a test is now also marked as risky when it specifies units of code using `@covers` or `@uses` that are not executed by the test

## [6.2.0] - 2017-06-02

### Added

* Implemented [#2642](https://github.com/sebastianbergmann/phpunit/pull/2642): Support counting non-`Iterator` `Traversable` objects
* Implemented [#2664](https://github.com/sebastianbergmann/phpunit/pull/2664): Add `@runClassInSeparateProcess` annotation to execute all tests of a class in a single, separate process
* Implemented [#2666](https://github.com/sebastianbergmann/phpunit/pull/2666): Allow using a `Traversable` as data provider (not only `Iterator`)
* Implemented [#2670](https://github.com/sebastianbergmann/phpunit/issues/2670): Add support for disabling the conversion of `E_DEPRECATED` to exceptions
* Implemented [#2692](https://github.com/sebastianbergmann/phpunit/issues/2692): Add `--no-logging` option to disable logging

### Changed

* Implemented [#2689](https://github.com/sebastianbergmann/phpunit/pull/2689): Unpack nested `IteratorAggregate` objects for `Count` constraint 
* When `beStrictAboutCoversAnnotation="true"` is configured or `--strict-coverage` is used then a test is now also marked as risky when it specifies units of code using `@covers` or `@uses` that are not executed by the test

[6.2.2]: https://github.com/sebastianbergmann/phpunit/compare/6.2.1...6.2.2
[6.2.1]: https://github.com/sebastianbergmann/phpunit/compare/6.2.0...6.2.1
[6.2.0]: https://github.com/sebastianbergmann/phpunit/compare/6.1...6.2.0

