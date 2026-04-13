--TEST--
Order by defects (with result cache): Top-level testsuite order is preserved when child suites contain equally-weighted defects
--FILE--
<?php declare(strict_types=1);
$testResultsFile = sys_get_temp_dir() . '/test-results';

if (file_exists($testResultsFile)) {
    unlink($testResultsFile);
}

copy(__DIR__ . '/fixture/two-suites-with-defects/test-results', $testResultsFile);

$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/fixture/two-suites-with-defects/phpunit.xml';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = sys_get_temp_dir();
$_SERVER['argv'][] = '--debug';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

unlink($testResultsFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (%s, 2 tests)
Test Suite Started (unit, 1 test)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\TwoSuites\UnitTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\TwoSuites\UnitTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\TwoSuites\UnitTest::testOne)
Test Failed (PHPUnit\TestFixture\ExecutionOrder\TwoSuites\UnitTest::testOne)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\ExecutionOrder\TwoSuites\UnitTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\TwoSuites\UnitTest, 1 test)
Test Suite Finished (unit, 1 test)
Test Suite Started (end-to-end, 1 test)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\TwoSuites\EndToEndTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\TwoSuites\EndToEndTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\TwoSuites\EndToEndTest::testOne)
Test Failed (PHPUnit\TestFixture\ExecutionOrder\TwoSuites\EndToEndTest::testOne)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\ExecutionOrder\TwoSuites\EndToEndTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\TwoSuites\EndToEndTest, 1 test)
Test Suite Finished (end-to-end, 1 test)
Test Suite Finished (%s, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
