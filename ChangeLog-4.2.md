# Changes in PHPUnit 4.2

All notable changes of the PHPUnit 4.2 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [4.2.5] - 2014-09-06

New release of PHPUnit as PHP Archive (PHAR) with updated dependencies

## [4.2.4] - 2014-08-31

### Fixed

* Fixed [#1413](https://github.com/sebastianbergmann/phpunit/issues/1413): `assertCount()` hangs in infinite loop on HHVM

## [4.2.3] - 2014-08-28

### Fixed

* Fixed [#1403](https://github.com/sebastianbergmann/phpunit/issues/1403): `phpunit --self-update` does not work

## [4.2.2] - 2014-08-18

### Fixed

* Fixed [#1399](https://github.com/sebastianbergmann/phpunit/issues/1399): `enforceTimeLimit` configuration option is not handled

## [4.2.1] - 2014-08-17

### Fixed

* Fixed [#1380](https://github.com/sebastianbergmann/phpunit/issues/1380): `assertMatch()` returns `Unexpected end tag : hr`
* Fixed [#1390](https://github.com/sebastianbergmann/phpunit/issues/1390): Licensing issue with third-party components bundled in PHAR distribution

## [4.2.0] - 2014-08-08

### Added

* Tests annotated with `@todo` will now be reported as risky when the `--disallow-todo-tests` option is used or `beStrictAboutTodoAnnotatedTests=true` is set in the configuration file
* The `atLeast()` and `atMost()` invocation count matchers were added

### Changed

* `trigger_error(__METHOD__ . ' is deprecated', E_USER_DEPRECATED);` is used now to indicate that a PHPUnit API method is deprecated; the old "system" for deprecating methods has been removed
* The PHP Archive (PHAR) distribution of PHPUnit can now be used as a library; `include()`ing or `require()`ing it will not execute the CLI test runner

### Deprecated

* The `assertTag()` and `assertSelect*()` assertion methods have been deprecated in favor of the [phpunit-dom-assertions](https://github.com/phpunit/phpunit-dom-assertions) extension; these methods will be removed in PHPUnit 5.0

[4.2.5]: https://github.com/sebastianbergmann/phpunit/compare/4.2.4...4.2.5
[4.2.4]: https://github.com/sebastianbergmann/phpunit/compare/4.2.3...4.2.4
[4.2.3]: https://github.com/sebastianbergmann/phpunit/compare/4.2.2...4.2.3
[4.2.2]: https://github.com/sebastianbergmann/phpunit/compare/4.2.1...4.2.2
[4.2.1]: https://github.com/sebastianbergmann/phpunit/compare/4.2.0...4.2.1
[4.2.0]: https://github.com/sebastianbergmann/phpunit/compare/4.1...4.2.0

