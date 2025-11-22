--TEST--
Repeat option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using PHP %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (RepeatTest, 4 tests)
Test Preparation Started (RepeatTest::test1)
Before Test Method Called (RepeatTest::setUp)
Before Test Method Finished:
- RepeatTest::setUp
Test Prepared (RepeatTest::test1)
Test Finished (RepeatTest::test1)
Test Preparation Started (RepeatTest::test1)
Before Test Method Called (RepeatTest::setUp)
Before Test Method Finished:
- RepeatTest::setUp
Test Prepared (RepeatTest::test1)
Test Passed (RepeatTest::test1)
Test Finished (RepeatTest::test1)
Test Preparation Started (RepeatTest::test2)
Before Test Method Called (RepeatTest::setUp)
Before Test Method Finished:
- RepeatTest::setUp
Test Prepared (RepeatTest::test2)
Test Finished (RepeatTest::test2)
Test Preparation Started (RepeatTest::test2)
Before Test Method Called (RepeatTest::setUp)
Before Test Method Finished:
- RepeatTest::setUp
Test Prepared (RepeatTest::test2)
Test Passed (RepeatTest::test2)
Test Finished (RepeatTest::test2)
Test Suite Finished (RepeatTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
