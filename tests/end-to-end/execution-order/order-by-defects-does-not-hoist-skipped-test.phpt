--TEST--
Order by defects (with result cache): Skipped tests are not hoisted to the front
--FILE--
<?php declare(strict_types=1);
$testResultsFile = sys_get_temp_dir() . '/test-results';

if (file_exists($testResultsFile)) {
    unlink($testResultsFile);
}

copy(__DIR__ . '/fixture/test-methods-with-skipped-and-failing/test-results', $testResultsFile);

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = sys_get_temp_dir();
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'defects';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-methods-with-skipped-and-failing';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

unlink($testResultsFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (CLI Arguments, 4 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testThree)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testThree)
Test Failed (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testThree)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testThree)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testTwo)
Test Skipped (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testTwo)
message
Test Finished (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testFour)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testFour)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testFour)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest::testFour)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\SkippedAndFailing\FooTest, 4 tests)
Test Suite Finished (CLI Arguments, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
