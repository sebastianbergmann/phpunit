# Changes in PHPUnit 4.7

## PHPUnit 4.7.0

* Tests not annotated with `@small`, `@medium`, or `@large` are no longer treated as being annotated with `@small`
* In verbose mode, the test runner now prints information about the PHP runtime
* To be consistent with the printing of PHP runtime information, the configuration file used is only printed in verbose mode
* A warning is now printed when code coverage data is collected but no whitelist is configured

