--TEST--
--repeat works with --filter to select specific tests
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testOne';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/SuccessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (2 tests)
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\SuccessTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 1 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 1 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 1 of 2))
Test Preparation Started (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 2 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 2 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 2 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\SuccessTest::testOne (repetition 2 of 2))
Test Suite Finished (PHPUnit\TestFixture\Repeat\SuccessTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
