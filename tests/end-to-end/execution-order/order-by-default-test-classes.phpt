--TEST--
Default order: Suite with test classes
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'default';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-classes';

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
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\BarTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\BarTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\BarTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\BarTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\BarTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\BarTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\FooTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\FooTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\FooTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\FooTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\FooTest::testTwo)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\FooTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\FooTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\FooTest, 2 tests)
Test Suite Finished (CLI Arguments, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
