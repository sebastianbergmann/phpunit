--TEST--
Order by duration (with test run history)
--FILE--
<?php declare(strict_types=1);
$testRunHistoryFile = sys_get_temp_dir() . '/test-run-history';

if (file_exists($testRunHistoryFile)) {
    unlink($testRunHistoryFile);
}

copy(__DIR__ . '/fixture/test-methods-with-duration/test-run-history', $testRunHistoryFile);

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = sys_get_temp_dir();
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'duration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-methods-with-duration';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

unlink($testRunHistoryFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Triggered PHPUnit Deprecation (Using "duration" for --order-by is deprecated and will be removed in PHPUnit 14. Use "duration-ascending" instead.)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (CLI Arguments, 3 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testTwo)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testThree)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testThree)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testThree)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testThree)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\Duration\FooTest, 3 tests)
Test Suite Finished (CLI Arguments, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
