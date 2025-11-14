--TEST--
Default order: Test methods with defects
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'default';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-methods-with-defects';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (4 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (CLI Arguments, 4 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testTwo)
Assertion Failed (Constraint: is true, Value: false)
Test Failed (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testTwo)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testThree)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testThree)
Test Errored (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testThree)
message
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testThree)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testFour)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testFour)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testFour)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest::testFour)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\FooTest, 4 tests)
Test Suite Finished (CLI Arguments, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
