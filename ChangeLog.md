PHPUnit 3.8
===========

This is the list of changes for the PHPUnit 3.8 release series.

PHPUnit 3.8.0
-------------

* Implemented #835: Printers that extend `PHPUnit_TextUI_ResultPrinter` should have similar construction
* Implemented #834: Added the `@requires OS` annotation.
* Implemented #773: Recursive and repeated arrays are more gracefully when comparison differences are exported.
* Implemented #758: Show a proper stack trace when @expectedException fails due to a unexcepted exception being thrown.
* Implemented #711: `coverage-text` now has an XML `showOnlySummary` option.
* Implemented #719: The `--stderr` flag now respects `--colors` and `--debug`.
* Implemented #382: Added the `$options` parameter to `PHPUnit_Framework_TestCase::getMockFromWsdl()` for configuring the `SoapClient`.
* A test will now fail in strict mode when it uses the `@covers` annotation and code that is not expected to be covered is executed.
* Fixed #240: XML strings are escaped by removing invalid characters.
* Fixed #261: `setUp()` and `setUpBeforeClass()` are run before filters are applied.
* Fixed #541: Excluded groups are counted towards total number of tests being executed.
* Fixed #789: PHP INI settings would not be passed to child processes.
* Fixed #806: Array references are now properly displayed in error output.
* Fixed #808: Resources are now reported as `resource(13) of type (stream)` instead of `NULL`.
* Fixed: `phpt` test cases now use the correct php binary when executed through wrapper scripts.
* PHPUnit 3.8 is only supported on PHP 5.4.7 (or later).
