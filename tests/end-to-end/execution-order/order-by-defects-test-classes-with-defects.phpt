--TEST--
Order by defects (with result cache): Test classes with defects
--FILE--
<?php declare(strict_types=1);
$testResultsFile = sys_get_temp_dir() . '/test-results';

if (file_exists($testResultsFile)) {
    unlink($testResultsFile);
}

copy(__DIR__ . '/fixture/test-classes-with-defects/test-results', $testResultsFile);

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = sys_get_temp_dir();
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'defects';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-classes-with-defects';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

unlink($testResultsFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (8 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (8 tests)
Test Suite Started (CLI Arguments, 8 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testThree)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testThree)
Test Errored (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testThree)
message
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testThree)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testTwo)
Assertion Failed (Constraint: is true, Value: false)
Test Failed (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testTwo)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testFour)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testFour)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testFour)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testFour)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\BarTest, 4 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testThree)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testThree)
Test Errored (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testThree)
message
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testThree)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testTwo)
Assertion Failed (Constraint: is true, Value: false)
Test Failed (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testTwo)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testFour)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testFour)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testFour)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest::testFour)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\Defects\BazTest, 4 tests)
Test Suite Finished (CLI Arguments, 8 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
