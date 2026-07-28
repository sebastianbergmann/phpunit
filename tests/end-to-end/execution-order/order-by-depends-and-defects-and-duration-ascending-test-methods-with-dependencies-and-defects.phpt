--TEST--
Order by depends,defects,duration-ascending (with test run history): Test methods with dependencies and defects
--FILE--
<?php declare(strict_types=1);
$testRunHistoryFile = sys_get_temp_dir() . '/test-run-history';

if (file_exists($testRunHistoryFile)) {
    unlink($testRunHistoryFile);
}

copy(__DIR__ . '/fixture/test-methods-with-dependencies-and-defects/test-run-history', $testRunHistoryFile);

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = sys_get_temp_dir();
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'depends,defects,duration-ascending';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/fixture/test-methods-with-dependencies-and-defects';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

unlink($testRunHistoryFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (CLI Arguments, 3 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest::testThree)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest::testThree)
Test Failed (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest::testThree)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest::testThree)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest::testTwo)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\DependenciesAndDefects\FooTest, 3 tests)
Test Suite Finished (CLI Arguments, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
