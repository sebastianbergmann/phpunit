# Changes in PHPUnit 13.0

All notable changes of the PHPUnit 13.0 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [13.0.0] - 2026-02-06

### Removed

* [#6054](https://github.com/sebastianbergmann/phpunit/issues/6054): `Assert::isType()`
* [#6057](https://github.com/sebastianbergmann/phpunit/issues/6057): `assertContainsOnly()` and `assertNotContainsOnly()`
* [#6061](https://github.com/sebastianbergmann/phpunit/issues/6061): `containsOnly()`
* [#6076](https://github.com/sebastianbergmann/phpunit/issues/6076): Support for PHP 8.3
* [#6141](https://github.com/sebastianbergmann/phpunit/issues/6141): `testClassName()` method on event value objects for hook methods called for test methods
* [#6230](https://github.com/sebastianbergmann/phpunit/issues/6230): `Configuration::includeTestSuite()` and `Configuration::excludeTestSuite()`
* [#6241](https://github.com/sebastianbergmann/phpunit/issues/6241): `--dont-report-useless-tests` CLI option
* [#6247](https://github.com/sebastianbergmann/phpunit/issues/6247): Support for using `#[CoversNothing]` on a test method
* [#6285](https://github.com/sebastianbergmann/phpunit/issues/6285): `#[RunClassInSeparateProcess]` attribute
* [#6356](https://github.com/sebastianbergmann/phpunit/issues/6356): Support for version constraint string argument without explicit version comparison operator

[13.0.0]: https://github.com/sebastianbergmann/phpunit/compare/12.5...main
