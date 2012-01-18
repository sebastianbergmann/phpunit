PHPUnit_MockObject 1.1
======================

This is the list of changes for the PHPUnit_MockObject 1.1 release series.

PHPUnit_MockObject 1.1.1
------------------------

* `getMockForAbstractClass()` now supports the stubbing and mocking of interfaces.
* Fixed an issue with `ReflectionClass::isCloneable()` not working correctly for internal classes in PHP 5.4.
* Fixed #46 `$this->any()` can now be used with parameter matchers.

PHPUnit_MockObject 1.1.0
------------------------

* Added `getObjectForTrait()` to support the testing of traits.
* Added `PHPUnit_Framework_MockObject_Stub_ReturnSelf` to support the stubbing of fluent interfaces.
* Added `PHPUnit_Framework_MockObject_Stub_ReturnValueMap` to support stubbing a method by returning a value from a map.
* `getMockForAbstractClass()` now supports the stubbing and mocking of concrete methods in abstract classes.
