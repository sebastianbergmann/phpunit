PHPUnit 3.6
===========

This is the list of changes for the PHPUnit 3.6 release series.

PHPUnit 3.6.0
-------------

* Added `assertCount()` and `assertNotCount()` to assert the number of elements in an array as well as `Countable` or `Iterator` objects.
* Added `returnSelf()` to ease the mocking and stubbing of fluent interfaces.
* Implemented GH-82: Test Skeleton Generator should create `@covers` annotations.
* `assertEquals()` now looks for (and invokes) a `__toString()` method when an object and string are compared.
* `@ticket` is now an alias for `@group`.
* The `PHPUnit_Extensions_Story_TestCase` functionality has been removed.
* The syntax check functionality has been removed.
