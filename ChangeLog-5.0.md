# Changes in PHPUnit 5.0

## PHPUnit 5.0.0

* Merged [#1753](https://github.com/sebastianbergmann/phpunit/issues/1753): Added the `assertFinite()`, `assertInfinite()` and `assertNan()` assertions
* Merged [#1781](https://github.com/sebastianbergmann/phpunit/issues/1781): Empty string is not treated as a valid JSON string anymore
* Implemented [#1656](https://github.com/sebastianbergmann/phpunit/issues/1656): Allow sorting test failures in reverse
* Implemented [#1780](https://github.com/sebastianbergmann/phpunit/issues/1780): Support for deep-cloning of results passed between tests using `@depends`
* The `assertSelectCount()`, `assertSelectRegExp()`, `assertSelectEquals()`, `assertTag()`, `assertNotTag()` assertions have been removed
* The PHPUnit_Selenium component is no longer bundled in the PHAR distribution
* The PHPUnit_Selenium component can no longer be configured using the `<selenium/browser>` element of PHPUnit's configuration file
* PHPUnit is no longer supported on PHP 5.3, PHP 5.4, and PHP 5.5

