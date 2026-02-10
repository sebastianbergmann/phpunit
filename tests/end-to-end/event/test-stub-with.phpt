--TEST--
The right events are emitted in the right order for a test that configures a test stub using with()
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/Example.php';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/StubWithTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\StubWithTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\StubWithTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\StubWithTest::testOne)
Test Stub Created (PHPUnit\TestFixture\Event\Example)
Test Triggered PHPUnit Deprecation (PHPUnit\TestFixture\Event\StubWithTest::testOne)
Using with() on a test stub has no effect and is deprecated.
With PHPUnit 13, it will not be possible to use with() on a test stub.
Test Passed (PHPUnit\TestFixture\Event\StubWithTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\StubWithTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\StubWithTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
