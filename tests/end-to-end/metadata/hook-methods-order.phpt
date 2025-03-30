--TEST--
The right events are emitted in the right order for a successful test that has a before-test method that is configured with annotation
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/../_files/HookMethodsOrderTestCase.php';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/HookMethodsOrderTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sHookMethodsOrderTestCase.php)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\HookMethodsOrderTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\HookMethodsOrderTest::testOne)
Before Test Method Called (PHPUnit\TestFixture\HookMethodsOrderTest::beforeWithPriorityInParent)
Before Test Method Called (PHPUnit\TestFixture\HookMethodsOrderTest::beforeWithPriority)
Before Test Method Called (PHPUnit\TestFixture\HookMethodsOrderTest::beforeInParent)
Before Test Method Called (PHPUnit\TestFixture\HookMethodsOrderTest::beforeFirst)
Before Test Method Called (PHPUnit\TestFixture\HookMethodsOrderTest::beforeSecond)
Before Test Method Called (PHPUnit\TestFixture\HookMethodsOrderTest::setUp)
Before Test Method Finished:
- PHPUnit\TestFixture\HookMethodsOrderTest::beforeWithPriorityInParent
- PHPUnit\TestFixture\HookMethodsOrderTest::beforeWithPriority
- PHPUnit\TestFixture\HookMethodsOrderTest::beforeInParent
- PHPUnit\TestFixture\HookMethodsOrderTest::beforeFirst
- PHPUnit\TestFixture\HookMethodsOrderTest::beforeSecond
- PHPUnit\TestFixture\HookMethodsOrderTest::setUp
Test Prepared (PHPUnit\TestFixture\HookMethodsOrderTest::testOne)
After Test Method Called (PHPUnit\TestFixture\HookMethodsOrderTest::afterWithPriority)
After Test Method Called (PHPUnit\TestFixture\HookMethodsOrderTest::afterWithPriorityInParent)
After Test Method Called (PHPUnit\TestFixture\HookMethodsOrderTest::tearDown)
After Test Method Called (PHPUnit\TestFixture\HookMethodsOrderTest::afterFirst)
After Test Method Called (PHPUnit\TestFixture\HookMethodsOrderTest::afterSecond)
After Test Method Called (PHPUnit\TestFixture\HookMethodsOrderTest::afterInParent)
After Test Method Finished:
- PHPUnit\TestFixture\HookMethodsOrderTest::afterWithPriority
- PHPUnit\TestFixture\HookMethodsOrderTest::afterWithPriorityInParent
- PHPUnit\TestFixture\HookMethodsOrderTest::tearDown
- PHPUnit\TestFixture\HookMethodsOrderTest::afterFirst
- PHPUnit\TestFixture\HookMethodsOrderTest::afterSecond
- PHPUnit\TestFixture\HookMethodsOrderTest::afterInParent
Test Passed (PHPUnit\TestFixture\HookMethodsOrderTest::testOne)
Test Finished (PHPUnit\TestFixture\HookMethodsOrderTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\HookMethodsOrderTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
