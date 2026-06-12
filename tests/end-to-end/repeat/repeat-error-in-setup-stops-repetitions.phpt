--TEST--
--repeat stops remaining repetitions after error in setUp()
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/ErrorInSetUpTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest, 3 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::testOne, 3 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::testOne (repetition 1 of 3))
Before Test Method Called (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::setUp)
Before Test Method Finished:
- PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::setUp
Test Prepared (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::testOne (repetition 1 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::testOne (repetition 1 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::testOne (repetition 1 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::testOne (repetition 2 of 3))
Before Test Method Called (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::setUp)
Before Test Method Errored (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::setUp)
Error in setUp() on second repetition
Before Test Method Finished:
- PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::setUp
Test Preparation Errored (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::testOne (repetition 2 of 3))
Error in setUp() on second repetition
Test Errored (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::testOne (repetition 2 of 3))
Error in setUp() on second repetition
Test Skipped (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::testOne (repetition 3 of 3))
Remaining repetition skipped after failure in repetition 2
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest::testOne, 3 repetitions)
Test Suite Finished (PHPUnit\TestFixture\Repeat\ErrorInSetUpTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
