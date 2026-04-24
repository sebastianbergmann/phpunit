--TEST--
--repeat with all tests passing
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/SuccessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (6 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (6 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\SuccessTest, 6 tests)
Test Preparation Started (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 1 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 1 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 1 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 1 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 2 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 2 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 2 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 2 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 3 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 3 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 3 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 3 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 1 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 1 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 1 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 1 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 2 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 2 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 2 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 2 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 3 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 3 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 3 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\SuccessTest::testTwo (repetition 3 of 3))
Test Suite Finished (PHPUnit\TestFixture\Repeat\SuccessTest, 6 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
