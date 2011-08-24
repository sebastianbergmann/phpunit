PHPUnit_MockObject 1.0
======================

This is the list of changes for the PHPUnit_MockObject 1.0 release series.

PHPUnit_MockObject 1.0.10
-------------------------

* Fixed GH-62: Generation of invalid PHP code when `getMock()` is called with invalid arguments.

PHPUnit_MockObject 1.0.9
------------------------

* Fixed GH-50: Error when trying to mock `isset()` or `echo()` methods.
* Mocking nonexistent classes in a namespace is now possible.

PHPUnit_MockObject 1.0.8
------------------------

* The blacklist of uncloneable classes did not work for classes that extend an uncloneable class.
* Updated list of dependencies in `package.xml`.

PHPUnit_MockObject 1.0.7
------------------------

* Fixed GH-38: Cannot mock methods that return a reference.

PHPUnit_MockObject 1.0.6
------------------------

* Fixed GH-35: Mocking undeclared methods impossible since 1.0.4.

PHPUnit_MockObject 1.0.5
------------------------

* Fixed GH-34: Mocking methods with variable parameter count impossible since 1.0.4.

PHPUnit_MockObject 1.0.4
------------------------

* Fixed GH-3: `returnCallback()` does not work for parameters that are passed by reference.

PHPUnit_MockObject 1.0.3
------------------------

* Added support for `getMockForAbstractClass()` to the Mock Builder API.

PHPUnit_MockObject 1.0.2
------------------------

* Fixed GH-27: Inconsistencies in template for mock classes.
* Improved fix for type hinting bug in `PHPUnit_Framework_MockObject_Generator::generateMock()`.

PHPUnit_MockObject 1.0.1
------------------------

* Fixed type hinting bug in `PHPUnit_Framework_MockObject_Generator::generateMock()`.

PHPUnit_MockObject 1.0.0
------------------------

* Initial release as separate component.
