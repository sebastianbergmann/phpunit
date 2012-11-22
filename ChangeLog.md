PHPUnit 3.8
===========

This is the list of changes for the PHPUnit 3.8 release series.

PHPUnit 3.8.0
-------------

* A test will now fail in strict mode when it uses the `@covers` annotation and code that is not expected to be covered is executed.
* Implemented #711: `coverage-text` now has an XML `showOnlySummary` option.
* Fixed: `phpt` test cases now use the correct php binary when executed through wrapper scripts.
* PHPUnit 3.8 is only supported on PHP 5.4.7 (or later).
