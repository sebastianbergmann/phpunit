--TEST--
Order by test size descending: Test methods within a single class
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'size-descending';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-methods-with-sizes';

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
Test Suite Started (CLI Arguments, 3 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest::testTwo)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest::testThree)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest::testThree)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest::testThree)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest::testThree)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\MethodSizes\FooTest, 3 tests)
Test Suite Finished (CLI Arguments, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
