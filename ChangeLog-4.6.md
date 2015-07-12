# Changes in PHPUnit 4.6

All notable changes of the PHPUnit 4.6 release series are documented in this file using the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [4.6.10] - 2015-06-03

### Changed

* Merged [#1693](https://github.com/sebastianbergmann/phpunit/pull/1693): Improved API documentation
* Merged [#1706](https://github.com/sebastianbergmann/phpunit/pull/1706): Avoid hard-coded URI to `phpunit.xsd`
* Merged [#1725](https://github.com/sebastianbergmann/phpunit/pull/1725): Update phpDox XSD URI
* Merged [#1735](https://github.com/sebastianbergmann/phpunit/pull/1735): Mute `chdir()` failures in XInclude handling of XML configuration file
* Merged [#1736](https://github.com/sebastianbergmann/phpunit/pull/1736): Verify that phar file can be overwritten before attempting self update

### Fixed

* Fixed [#1737](https://github.com/sebastianbergmann/phpunit/issues/1737): Confusing output from `--testdox` for empty test class

## [4.6.9] - 2015-05-29

### Fixed

* Fixed [#1731](https://github.com/sebastianbergmann/phpunit/issues/1731): `.` after failure count has no background color when `--colors` is used

## [4.6.8] - 2015-05-28

New PHAR release due to updated dependencies

## [4.6.7] - 2015-05-25

New PHAR release due to updated dependencies

## [4.6.6] - 2015-04-29

### Fixed

* Fixed [#1684](https://github.com/sebastianbergmann/phpunit/issues/1684): PHAR does not work on HHVM

## [4.6.5] - 2015-04-29

* Fixed [#1677](https://github.com/sebastianbergmann/phpunit/issues/1677): Number of risky tests not printed when there are failing tests
* Fixed [#1688](https://github.com/sebastianbergmann/phpunit/issues/1688): Self-Update operation does not work due to outdated SSL certificate

## [4.6.4] - 2015-04-11

### Changed

* The default list of blacklisted classes is now always passed to PHP_CodeCoverage

## [4.6.3] - 2015-04-11

### Changed

* Updated the default list of blacklisted classes

## [4.6.2] - 2015-04-07

### Fixed

* Fixed [#1667](https://github.com/sebastianbergmann/phpunit/issues/1667): Loading `src/Framework/Assert/Functions.php` by default causes collisions

## [4.6.1] - 2015-04-03

### Fixed

* Fixed [#1665](https://github.com/sebastianbergmann/phpunit/issues/1665): PHPUnit 4.6.0 PHAR does not work when renamed to `phpunit`

## [4.6.0] - 2015-04-03

### Added

* Added the `--strict-global-state` command-line option and the `beStrictAboutChangesToGlobalState` configuration setting for enabling a check that global variabes, super-global variables, and static attributes in user-defined classes are not modified during a test
* Merged [#1527](https://github.com/sebastianbergmann/phpunit/issues/1527) and [#1529](https://github.com/sebastianbergmann/phpunit/issues/1529): Allow to define options for displaying colors

### Changed

* Merged [#1528](https://github.com/sebastianbergmann/phpunit/issues/1528): Improve message when `PHPUnit_Framework_Constraint_Count` is used with logical operators

### Fixed

* Merged [#1537](https://github.com/sebastianbergmann/phpunit/issues/1537): Fix problem of `--stderr` with `--tap` and `--testdox`
* Fixed [#1599](https://github.com/sebastianbergmann/phpunit/issues/1599): The PHAR build of PHPUnit no longer uses an autoloader to load PHPUnit's own classes and instead statically loads all classes on startup

[4.6.10]: https://github.com/sebastianbergmann/phpunit/compare/4.6.9...4.6.10
[4.6.9]: https://github.com/sebastianbergmann/phpunit/compare/4.6.8...4.6.9
[4.6.8]: https://github.com/sebastianbergmann/phpunit/compare/4.6.7...4.6.8
[4.6.7]: https://github.com/sebastianbergmann/phpunit/compare/4.6.6...4.6.7
[4.6.6]: https://github.com/sebastianbergmann/phpunit/compare/4.6.5...4.6.6
[4.6.5]: https://github.com/sebastianbergmann/phpunit/compare/4.6.4...4.6.5
[4.6.4]: https://github.com/sebastianbergmann/phpunit/compare/4.6.3...4.6.4
[4.6.3]: https://github.com/sebastianbergmann/phpunit/compare/4.6.2...4.6.3
[4.6.2]: https://github.com/sebastianbergmann/phpunit/compare/4.6.1...4.6.2
[4.6.1]: https://github.com/sebastianbergmann/phpunit/compare/4.6.0...4.6.1
[4.6.0]: https://github.com/sebastianbergmann/phpunit/compare/4.5...4.6.0

