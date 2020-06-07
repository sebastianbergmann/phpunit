# Changes in PHPUnit 9.3

All notable changes of the PHPUnit 9.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [9.3.0] - 2020-08-07

### Changed

* [#4264](https://github.com/sebastianbergmann/phpunit/pull/4264): Refactor logical operator constraints
* `PHPUnit\Framework\TestCase::$backupGlobalsBlacklist` is deprecated, use `PHPUnit\Framework\TestCase::$backupGlobalsExcludeList` instead
* `PHPUnit\Framework\TestCase::$backupStaticAttributesBlacklist` is deprecated, use `PHPUnit\Framework\TestCase::$backupStaticAttributesExcludeList` instead
* `PHPUnit\Util\Blacklist` is now deprecated, please use `PHPUnit\Util\ExcludeList` instead

[9.3.0]: https://github.com/sebastianbergmann/phpunit/compare/9.2...master
