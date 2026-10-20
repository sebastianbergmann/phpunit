--TEST--
The garbage collections that are triggered while the examples of a property are run, while the example that falsified it is replayed, or while its counterexample is shrunk, are reported when the garbage collector is controlled
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/garbage-collection-while-examples-are-run';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Runner Disabled Garbage Collection
Test Runner Triggered Garbage Collection
Test Suite Started (%s%ephpunit.xml, 2 tests)
Test Suite Started (default, 2 tests)
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest, 2 tests)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatHolds, 4 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatHolds)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatHolds)
Test Runner Triggered Garbage Collection
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatHolds)
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatHolds, seed: %d, examples: 4, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatHolds)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatHolds, 4 examples)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatIsFalsified, 4 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatIsFalsified)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatIsFalsified)
Test Runner Triggered Garbage Collection
Test Runner Triggered Garbage Collection
Test Runner Triggered Garbage Collection
Test Property Falsified (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatIsFalsified, seed: %d, examples: 1, shrinking steps: 0, shrinking attempts: 2)
$value = true;
Test Failed (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatIsFalsified)
Failed asserting that true is false.
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatIsFalsified, seed: %d, examples: 1, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatIsFalsified)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest::testPropertyThatIsFalsified, 4 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\GarbageCollectionWhileExamplesAreRun\GarbageCollectionWhileExamplesAreRunTest, 2 tests)
Test Suite Finished (default, 2 tests)
Test Suite Finished (%s%ephpunit.xml, 2 tests)
Test Runner Execution Finished
Test Runner Triggered Garbage Collection
Test Runner Enabled Garbage Collection
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
