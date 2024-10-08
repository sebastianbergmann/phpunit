--TEST--
The right events are emitted in the right order for a test that uses a mock object
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/Example.php';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/MockTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\MockTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\MockTest::testSuccess)
Test Prepared (PHPUnit\TestFixture\Event\MockTest::testSuccess)
Mock Object Created (PHPUnit\TestFixture\Event\Example)
Test Passed (PHPUnit\TestFixture\Event\MockTest::testSuccess)
Test Finished (PHPUnit\TestFixture\Event\MockTest::testSuccess)
Test Suite Finished (PHPUnit\TestFixture\Event\MockTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
