--TEST--
The right events are emitted in the right order for a successful test that has a before-test method that is configured with attribute
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/BeforeTestMethodWithPrioritizedAttributeTest.php';

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
Test Suite Started (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithPrioritizedAttributeTest, 1 test)
Test Preparation Started (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithPrioritizedAttributeTest::testOne)
Before Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithPrioritizedAttributeTest::beforeMethodWithHighPriority)
Before Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithPrioritizedAttributeTest::setUp)
Before Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithPrioritizedAttributeTest::beforeMethodWithLowPriority)
Before Test Method Finished:
- PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithPrioritizedAttributeTest::beforeMethodWithHighPriority
- PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithPrioritizedAttributeTest::setUp
- PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithPrioritizedAttributeTest::beforeMethodWithLowPriority
Test Prepared (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithPrioritizedAttributeTest::testOne)
Test Passed (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithPrioritizedAttributeTest::testOne)
Test Finished (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithPrioritizedAttributeTest::testOne)
Test Suite Finished (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithPrioritizedAttributeTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
