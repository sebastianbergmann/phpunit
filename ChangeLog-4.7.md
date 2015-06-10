# Changes in PHPUnit 4.7

## PHPUnit 4.7.3

* Fixed [#1317](https://github.com/sebastianbergmann/phpunit/issues/1317): JUnit XML logfiles does not contain warnings
* Fixed an [issue](https://github.com/sebastianbergmann/php-code-coverage/issues/347) where the warning that no whitelist is used is displayed when it should not

## PHPUnit 4.7.2

* New PHAR release due to updated dependencies

## PHPUnit 4.7.1

* New PHAR release due to updated dependencies

## PHPUnit 4.7.0

* Merged [#1718](https://github.com/sebastianbergmann/phpunit/issues/1718): Support for `--INI--` section in PHPT tests
* Tests not annotated with `@small`, `@medium`, or `@large` are no longer treated as being annotated with `@small`
* In verbose mode, the test runner now prints information about the PHP runtime
* To be consistent with the printing of PHP runtime information, the configuration file used is only printed in verbose mode
* A warning is now printed when code coverage data is collected but no whitelist is configured

