--TEST--
Default order: Test methods with dependencies
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'default';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-methods-with-dependencies/FooTest.php';

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
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\Dependencies\FooTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Dependencies\FooTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Dependencies\FooTest::testTwo)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Dependencies\FooTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Dependencies\FooTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Dependencies\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Dependencies\FooTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Dependencies\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Dependencies\FooTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\Dependencies\FooTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
