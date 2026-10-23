--TEST--
The right events are emitted in the right order for issues that are triggered in setUp() and tearDown()
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/UserDeprecationInBeforeAndAfterTestMethodsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\UserDeprecationInBeforeAndAfterTestMethodsTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\UserDeprecationInBeforeAndAfterTestMethodsTest::testOne)
Before Test Method Called (PHPUnit\TestFixture\Event\UserDeprecationInBeforeAndAfterTestMethodsTest::setUp)
Test Triggered Deprecation (PHPUnit\TestFixture\Event\UserDeprecationInBeforeAndAfterTestMethodsTest::testOne, unknown if issue was triggered in first-party code or third-party code) in %s:%d
deprecation in setUp()
Before Test Method Finished:
- PHPUnit\TestFixture\Event\UserDeprecationInBeforeAndAfterTestMethodsTest::setUp
Test Prepared (PHPUnit\TestFixture\Event\UserDeprecationInBeforeAndAfterTestMethodsTest::testOne)
After Test Method Called (PHPUnit\TestFixture\Event\UserDeprecationInBeforeAndAfterTestMethodsTest::tearDown)
Test Triggered Deprecation (PHPUnit\TestFixture\Event\UserDeprecationInBeforeAndAfterTestMethodsTest::testOne, unknown if issue was triggered in first-party code or third-party code) in %s:%d
deprecation in tearDown()
After Test Method Finished:
- PHPUnit\TestFixture\Event\UserDeprecationInBeforeAndAfterTestMethodsTest::tearDown
Test Passed (PHPUnit\TestFixture\Event\UserDeprecationInBeforeAndAfterTestMethodsTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\UserDeprecationInBeforeAndAfterTestMethodsTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\UserDeprecationInBeforeAndAfterTestMethodsTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
