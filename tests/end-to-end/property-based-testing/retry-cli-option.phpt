--TEST--
The --retry CLI option does not apply to a property, and no warning is emitted for it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--retry';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/_files/PassingPropertyTest.php';

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
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\PassingPropertyTest, 1 test)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\PassingPropertyTest::testReversingTwiceIsIdentity, 10 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\PassingPropertyTest::testReversingTwiceIsIdentity)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\PassingPropertyTest::testReversingTwiceIsIdentity)
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\PassingPropertyTest::testReversingTwiceIsIdentity)
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\PassingPropertyTest::testReversingTwiceIsIdentity, seed: %d, examples: 10, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\PassingPropertyTest::testReversingTwiceIsIdentity)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\PassingPropertyTest::testReversingTwiceIsIdentity, 10 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\PassingPropertyTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
