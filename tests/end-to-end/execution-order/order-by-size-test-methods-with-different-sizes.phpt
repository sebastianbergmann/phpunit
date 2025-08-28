--TEST--
Order by test size: Test class with test methods that have different sizes
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'size';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-methods-with-different-sizes';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (3 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (CLI Arguments, 3 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest::testThree)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest::testThree)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest::testThree)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest::testThree)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest::testTwo)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\SizesTest, 3 tests)
Test Suite Finished (CLI Arguments, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
