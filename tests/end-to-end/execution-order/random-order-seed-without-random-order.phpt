--TEST--
--random-order-seed without --order-by random triggers warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--random-order-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-classes-with-duration';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (%s)
Test Runner Triggered PHPUnit Warning (--random-order-seed is only used when execution order is "random" (use --order-by random or --random-order))
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (6 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (6 tests)
Test Suite Started (CLI Arguments, 6 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testTwo)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testThree)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testThree)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testThree)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testThree)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest, 3 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testTwo)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testThree)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testThree)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testThree)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testThree)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest, 3 tests)
Test Suite Finished (CLI Arguments, 6 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
