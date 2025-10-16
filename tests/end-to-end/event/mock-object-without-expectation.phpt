--TEST--
The right events are emitted in the right order for a test that uses a mock object
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/Example.php';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/MockWithoutExpectationTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sExample.php)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (TestFixture\PHPUnit\Event\MockWithoutExpectationTest, 1 test)
Test Preparation Started (TestFixture\PHPUnit\Event\MockWithoutExpectationTest::testSuccess)
Test Prepared (TestFixture\PHPUnit\Event\MockWithoutExpectationTest::testSuccess)
Mock Object Created (PHPUnit\TestFixture\Event\Example)
Test Triggered PHPUnit Notice (TestFixture\PHPUnit\Event\MockWithoutExpectationTest::testSuccess)
No expectations were configured for the mock object for PHPUnit\TestFixture\Event\Example. You should refactor your test code and use a test stub instead.
Test Passed (TestFixture\PHPUnit\Event\MockWithoutExpectationTest::testSuccess)
Test Finished (TestFixture\PHPUnit\Event\MockWithoutExpectationTest::testSuccess)
Test Suite Finished (TestFixture\PHPUnit\Event\MockWithoutExpectationTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
