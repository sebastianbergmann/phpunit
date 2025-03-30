--TEST--
The right events are emitted in the right order for a test that fails because of an assertion failure in a "before test" method
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/AssertionFailureInSetUpTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\AssertionFailureInSetUpTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\AssertionFailureInSetUpTest::testOne)
Before Test Method Called (PHPUnit\TestFixture\Event\AssertionFailureInSetUpTest::beforeTest)
Before Test Method Errored (PHPUnit\TestFixture\Event\AssertionFailureInSetUpTest::beforeTest)
Failed asserting that false is true.
Before Test Method Finished:
- PHPUnit\TestFixture\Event\AssertionFailureInSetUpTest::beforeTest
Test Preparation Failed (PHPUnit\TestFixture\Event\AssertionFailureInSetUpTest::testOne)
Test Failed (PHPUnit\TestFixture\Event\AssertionFailureInSetUpTest::testOne)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Event\AssertionFailureInSetUpTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\AssertionFailureInSetUpTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
