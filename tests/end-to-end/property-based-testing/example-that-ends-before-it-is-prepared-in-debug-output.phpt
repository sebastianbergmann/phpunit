--TEST--
An example that errors before it is prepared is finished without being reported as prepared, and it is not counted as an example for which the property was checked
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testErrorInSetUp';
$_SERVER['argv'][] = __DIR__ . '/_files/ExampleThatEndsBeforeItIsPreparedTest.php';

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
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest, 1 test)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest::testErrorInSetUp, 100 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest::testErrorInSetUp)
Before Test Method Called (PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest::setUp)
Before Test Method Errored (PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest::setUp)
error in setUp()
Before Test Method Finished:
- PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest::setUp
Test Preparation Errored (PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest::testErrorInSetUp)
error in setUp()
Test Errored (PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest::testErrorInSetUp)
error in setUp()
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest::testErrorInSetUp, seed: %d, examples: 0, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest::testErrorInSetUp)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest::testErrorInSetUp, 100 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExampleThatEndsBeforeItIsPreparedTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
