--TEST--
A property is skipped when one of its examples is skipped, and no further examples are checked
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/SkippedExampleTest.php';

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
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\SkippedExampleTest, 1 test)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\SkippedExampleTest::testSkipsTheExample, 10 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\SkippedExampleTest::testSkipsTheExample)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\SkippedExampleTest::testSkipsTheExample)
Test Skipped (PHPUnit\TestFixture\PropertyBasedTesting\SkippedExampleTest::testSkipsTheExample)
skipped
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\SkippedExampleTest::testSkipsTheExample, seed: %d, examples: 1, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\SkippedExampleTest::testSkipsTheExample)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\SkippedExampleTest::testSkipsTheExample, 10 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\SkippedExampleTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
