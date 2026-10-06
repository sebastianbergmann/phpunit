--TEST--
executionOrder="size-ascending" in phpunit.xml: Test suites with test classes that have different sizes
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/fixture/xml-size-ascending/phpunit.xml';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';

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
Test Suite Started (%s%ephpunit.xml, 3 tests)
Test Suite Started (unit, 2 tests)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\UnitTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\IntegrationTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\IntegrationTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\IntegrationTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\IntegrationTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\IntegrationTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\IntegrationTest, 1 test)
Test Suite Finished (unit, 2 tests)
Test Suite Started (end-to-end, 1 test)
Test Suite Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\EndToEndTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\EndToEndTest::testOne)
Test Prepared (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\EndToEndTest::testOne)
Test Passed (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\EndToEndTest::testOne)
Test Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\EndToEndTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ExecutionOrder\DifferentSizes\EndToEndTest, 1 test)
Test Suite Finished (end-to-end, 1 test)
Test Suite Finished (%s%ephpunit.xml, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
