--TEST--
Random order: Suite with test classes
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'random';
$_SERVER['argv'][] = '--random-order-seed';
$_SERVER['argv'][] = '1758723977';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-classes-with-duration';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (6 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (6 tests)
Test Suite Started (CLI Arguments, 6 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testThree)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testThree)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testThree)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testThree)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testTwo)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest, 3 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testTwo)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testThree)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testThree)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testThree)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testThree)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\BarTest, 3 tests)
Test Suite Finished (CLI Arguments, 6 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
