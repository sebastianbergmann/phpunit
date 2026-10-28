--TEST--
The Test\PropertyChecked event has the highest score that the examples of a property that were checked targeted with each label, without the explicit examples
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/TargetedScoreTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\TargetedScoreTest, 1 test)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\TargetedScoreTest::testTargetsTheValue, 20 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\TargetedScoreTest::testTargetsTheValue)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\TargetedScoreTest::testTargetsTheValue)
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\TargetedScoreTest::testTargetsTheValue)
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\TargetedScoreTest::testTargetsTheValue, seed: %d, examples: 20, explicit examples: 1, rejected: 0)
value: highest targeted score 1000
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\TargetedScoreTest::testTargetsTheValue)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\TargetedScoreTest::testTargetsTheValue, 20 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\TargetedScoreTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
