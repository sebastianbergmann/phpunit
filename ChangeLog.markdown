PHPUnit 3.6
===========

This is the list of changes for the PHPUnit 3.6 release series.

PHPUnit 3.6.0
-------------

* Added `assertCount()` and `assertNotCount()` to assert the number of elements in an array as well as `Countable` or `Iterator` objects.
* Added `returnSelf()` to ease the mocking and stubbing of fluent interfaces.
* Implemented GH-82: Test Skeleton Generator should create `@covers` annotations.
* Implemented GH-83: Test errors and failures as well as incomplete and skipped tests now get coloured letters in the test progress.
* Implemented GH-88: `@expectedException` (and `setExpectedException()`) no longer accept `Exception` as the expected exception class.
* `assertEquals()` now looks for (and invokes) a `__toString()` method when an object and string are compared.
* Using the `@small` (alias for `@group small`), `@medium` (alias for `@group medium`), and `@large` (alias for `@group large`) annotations, a test can now be marked to be of a certain size. By default, a test is "small".
* A test must not `@depend` on a test that is larger than itself.
* `@ticket` is now an alias for `@group`.
* `assertType()` and `assertNotType()` as well as `assertAttributeType()` and `assertAttributeNotType()` have been removed. `assertInternalType()` should be used for asserting internal types such as `integer` or `string` whereas `assertInstanceOf()` should be used for asserting that an object is an instance of a specified class or interface.
* The `PHPUnit_Extensions_Story_TestCase` functionality has been moved to a separate package.
* The syntax check functionality has been removed.
