# Changes in PHPUnit 4.6

## PHPUnit 4.6.0

* Merged [#1527](https://github.com/sebastianbergmann/phpunit/issues/1527) and [#1529](https://github.com/sebastianbergmann/phpunit/issues/1529): Allow to define options for displaying colors
* Merged [#1528](https://github.com/sebastianbergmann/phpunit/issues/1528): Improve message when `PHPUnit_Framework_Constraint_Count` is used with logical operators
* Merged [#1537](https://github.com/sebastianbergmann/phpunit/issues/1537): Fix problem of `--stderr` with `--tap` and `--testdox`
* Fixed [#1599](https://github.com/sebastianbergmann/phpunit/issues/1599): The PHAR build of PHPUnit no longer uses an autoloader to load PHPUnit's own classes and instead statically loads all classes on startup

