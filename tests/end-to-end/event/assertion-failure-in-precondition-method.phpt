--TEST--
The right events are emitted in the right order for a test that fails because of an assertion failure in a precondition method
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/AssertionFailureInPreConditionTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (1 test)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\AssertionFailureInPreConditionTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\AssertionFailureInPreConditionTest::testOne)
Assertion Failed (Constraint: is true, Value: false)
Pre Condition Method Called (PHPUnit\TestFixture\Event\AssertionFailureInPreConditionTest::preCondition)
Pre Condition Method Finished:
- PHPUnit\TestFixture\Event\AssertionFailureInPreConditionTest::preCondition
Test Preparation Failed (PHPUnit\TestFixture\Event\AssertionFailureInPreConditionTest::testOne)
Test Failed (PHPUnit\TestFixture\Event\AssertionFailureInPreConditionTest::testOne)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Event\AssertionFailureInPreConditionTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\AssertionFailureInPreConditionTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
