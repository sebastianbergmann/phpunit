--TEST--
The right events are emitted in the right order for a test that fails because of an assertion failure in a "post condition" method
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/AssertionFailureInPostConditionTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\AssertionFailureInPostConditionTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\AssertionFailureInPostConditionTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\AssertionFailureInPostConditionTest::testOne)
Post Condition Method Called (PHPUnit\TestFixture\Event\AssertionFailureInPostConditionTest::postCondition)
Post Condition Method Errored (PHPUnit\TestFixture\Event\AssertionFailureInPostConditionTest::postCondition)
Failed asserting that false is true.
Post Condition Method Finished:
- PHPUnit\TestFixture\Event\AssertionFailureInPostConditionTest::postCondition
Test Failed (PHPUnit\TestFixture\Event\AssertionFailureInPostConditionTest::testOne)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Event\AssertionFailureInPostConditionTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\AssertionFailureInPostConditionTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
