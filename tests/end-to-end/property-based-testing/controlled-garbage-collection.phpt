--TEST--
The examples of a property, but not the property itself, count toward the number of tests after which garbage is collected when the garbage collector is controlled
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/controlled-garbage-collection';

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
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection\GarbageCollectionTest, 2 tests)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection\GarbageCollectionTest::testProperty, 2 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection\GarbageCollectionTest::testProperty)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection\GarbageCollectionTest::testProperty)
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection\GarbageCollectionTest::testProperty)
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection\GarbageCollectionTest::testProperty, seed: %d, examples: 2, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection\GarbageCollectionTest::testProperty)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection\GarbageCollectionTest::testProperty, 2 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection\GarbageCollectionTest::testTest)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection\GarbageCollectionTest::testTest)
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection\GarbageCollectionTest::testTest)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection\GarbageCollectionTest::testTest)
Test Runner Triggered Garbage Collection
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\ControlledGarbageCollection\GarbageCollectionTest, 2 tests)
Test Suite Finished (default, 2 tests)
Test Suite Finished (%s%ephpunit.xml, 2 tests)
Test Runner Execution Finished
Test Runner Triggered Garbage Collection
Test Runner Enabled Garbage Collection
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
