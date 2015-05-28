# Changes in PHPUnit 4.6

## PHPUnit 4.6.9

* Fixed [#1731](https://github.com/sebastianbergmann/phpunit/issues/1731): `.` after failure count has no background color when `--colors` is used

## PHPUnit 4.6.8

* New PHAR release due to updated dependencies

## PHPUnit 4.6.7

* New PHAR release due to updated dependencies

## PHPUnit 4.6.6

* Fixed [#1684](https://github.com/sebastianbergmann/phpunit/issues/1684): PHAR does not work on HHVM

## PHPUnit 4.6.5

* Fixed [#1677](https://github.com/sebastianbergmann/phpunit/issues/1677): Number of risky tests not printed when there are failing tests
* Fixed [#1688](https://github.com/sebastianbergmann/phpunit/issues/1688): Self-Update operation does not work due to outdated SSL certificate

## PHPUnit 4.6.4

* The default list of blacklisted classes is now always passed to PHP_CodeCoverage

## PHPUnit 4.6.3

* Updated the default list of blacklisted classes

## PHPUnit 4.6.2

* Fixed [#1667](https://github.com/sebastianbergmann/phpunit/issues/1667): Loading `src/Framework/Assert/Functions.php` by default causes collisions

## PHPUnit 4.6.1

* Fixed [#1665](https://github.com/sebastianbergmann/phpunit/issues/1665): PHPUnit 4.6.0 PHAR does not work when renamed to `phpunit`

## PHPUnit 4.6.0

* Added the `--strict-global-state` command-line option and the `beStrictAboutChangesToGlobalState` configuration setting for enabling a check that global variabes, super-global variables, and static attributes in user-defined classes are not modified during a test
* Merged [#1527](https://github.com/sebastianbergmann/phpunit/issues/1527) and [#1529](https://github.com/sebastianbergmann/phpunit/issues/1529): Allow to define options for displaying colors
* Merged [#1528](https://github.com/sebastianbergmann/phpunit/issues/1528): Improve message when `PHPUnit_Framework_Constraint_Count` is used with logical operators
* Merged [#1537](https://github.com/sebastianbergmann/phpunit/issues/1537): Fix problem of `--stderr` with `--tap` and `--testdox`
* Fixed [#1599](https://github.com/sebastianbergmann/phpunit/issues/1599): The PHAR build of PHPUnit no longer uses an autoloader to load PHPUnit's own classes and instead statically loads all classes on startup

