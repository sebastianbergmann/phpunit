# Changes in PHPUnit 10.3

All notable changes of the PHPUnit 10.3 release series are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [10.3.0] - 2023-08-04

### Changed

* When a test case class inherits test methods from a parent class then, by default (when no test reordering is requested), the test methods from the class that is highest in the inheritance tree (instead of the class that is lowest in the inheritance tree) are now run first
* Invocation count expectation failure messages have been slightly improved
* When a test case class inherits test methods from a parent class then the TestDox output for such a test case class now starts with test methods from the class that is highest in the inheritance tree (instead of the class that is lowest in the inheritance tree)

* [10.3.0]: https://github.com/sebastianbergmann/phpunit/compare/10.2...main
