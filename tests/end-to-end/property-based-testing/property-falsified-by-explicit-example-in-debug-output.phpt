--TEST--
The Test\PropertyFalsified event for a property that is falsified by an explicit example names that example, and no examples are generated
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testAbsoluteValueIsTheValue';
$_SERVER['argv'][] = __DIR__ . '/_files/PropertyFalsifiedByExplicitExampleTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (1 test)
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\PropertyFalsifiedByExplicitExampleTest, 1 test)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\PropertyFalsifiedByExplicitExampleTest::testAbsoluteValueIsTheValue, 100 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\PropertyFalsifiedByExplicitExampleTest::testAbsoluteValueIsTheValue)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\PropertyFalsifiedByExplicitExampleTest::testAbsoluteValueIsTheValue)
Test Property Falsified (PHPUnit\TestFixture\PropertyBasedTesting\PropertyFalsifiedByExplicitExampleTest::testAbsoluteValueIsTheValue, explicit example "minus one")
$value = -1;
Test Failed (PHPUnit\TestFixture\PropertyBasedTesting\PropertyFalsifiedByExplicitExampleTest::testAbsoluteValueIsTheValue)
Failed asserting that 1 is identical to -1.
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\PropertyFalsifiedByExplicitExampleTest::testAbsoluteValueIsTheValue, seed: %d, examples: 0, explicit examples: 2, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\PropertyFalsifiedByExplicitExampleTest::testAbsoluteValueIsTheValue)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\PropertyFalsifiedByExplicitExampleTest::testAbsoluteValueIsTheValue, 100 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\PropertyFalsifiedByExplicitExampleTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
