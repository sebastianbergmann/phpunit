--TEST--
The right events are emitted in the right order for a successful test that has a before-test method that is configured with annotation
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/../_files/HookMethodsOrderTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (1 test)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest, 1 test)
Test Preparation Started (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::testOne)
Before Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::beforeWithPriorityInParent)
Before Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::beforeWithPriority)
Before Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::beforeInParent)
Before Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::beforeFirst)
Before Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::beforeSecond)
Before Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::setUp)
Before Test Method Finished:
- PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::beforeWithPriorityInParent
- PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::beforeWithPriority
- PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::beforeInParent
- PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::beforeFirst
- PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::beforeSecond
- PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::setUp
Test Prepared (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::testOne)
Test Passed (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::testOne)
After Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::afterWithPriority)
After Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::afterWithPriorityInParent)
After Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::tearDown)
After Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::afterFirst)
After Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::afterSecond)
After Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::afterInParent)
After Test Method Finished:
- PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::afterWithPriority
- PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::afterWithPriorityInParent
- PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::tearDown
- PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::afterFirst
- PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::afterSecond
- PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::afterInParent
Test Finished (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest::testOne)
Test Suite Finished (PHPUnit\DeprecatedAnnotationsTestFixture\HookMethodsOrderTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
