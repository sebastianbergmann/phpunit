--TEST--
Reverse order: Test methods
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'reverse';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-classes/FooTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (2 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\FooTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\FooTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\FooTest::testTwo)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\FooTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\FooTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\FooTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\FooTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\FooTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
