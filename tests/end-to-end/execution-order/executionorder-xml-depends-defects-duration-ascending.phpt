--TEST--
executionOrder="depends,defects,duration-ascending" in phpunit.xml (with test run history): Test methods with dependencies and defects
--FILE--
<?php declare(strict_types=1);
$testRunHistoryFile = sys_get_temp_dir() . '/test-run-history';

if (file_exists($testRunHistoryFile)) {
    unlink($testRunHistoryFile);
}

copy(__DIR__ . '/fixture/xml-depends-defects-duration-ascending/test-run-history', $testRunHistoryFile);

$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/fixture/xml-depends-defects-duration-ascending/phpunit.xml';
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = sys_get_temp_dir();
$_SERVER['argv'][] = '--debug';

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
Test Suite Started (%s, 3 tests)
Test Suite Started (default, 3 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest::testThree)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest::testThree)
Test Failed (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest::testThree)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest::testThree)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest::testTwo)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest::testTwo)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest::testTwo)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\XmlDependsDefectsDurationAscending\FooTest, 3 tests)
Test Suite Finished (default, 3 tests)
Test Suite Finished (%s, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
