--TEST--
Default order: Suite with test classes that have different sizes
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'default';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-classes-with-different-sizes';

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
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\EndToEndTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\EndToEndTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\EndToEndTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\EndToEndTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\EndToEndTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\EndToEndTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\IntegrationTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\IntegrationTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\IntegrationTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\IntegrationTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\IntegrationTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\IntegrationTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest, 1 test)
Test Suite Finished (CLI Arguments, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
