PHPUnit_MockObject 1.2
======================

This is the list of changes for the PHPUnit_MockObject 1.2 release series.

PHPUnit_MockObject 1.2.3
------------------------

* Fixed a bug where getting two mocks with different argument cloning options returned the same mock.

PHPUnit_MockObject 1.2.2
------------------------

* Fixed #100: Removed the unique mock object ID introduced in version 1.2.

PHPUnit_MockObject 1.2.1
------------------------

* No changes.

PHPUnit_MockObject 1.2.0
------------------------

* Implemented #47: Make cloning of arguments passed to mocked methods optional.
* Implemented #84: `getMockFromWsdl()` now works with namespaces.
* Fixed #90: Mocks with a fixed class name could only be created once.

