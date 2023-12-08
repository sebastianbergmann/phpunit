--TEST--
The right events are emitted in the right order for a test that uses a test stub
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/StubTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\StubTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\StubTest::testSuccess)
Test Prepared (PHPUnit\TestFixture\Event\StubTest::testSuccess)
Test Stub Created (PHPUnit\TestFixture\Event\Example)
Test Passed (PHPUnit\TestFixture\Event\StubTest::testSuccess)
Test Finished (PHPUnit\TestFixture\Event\StubTest::testSuccess)
Test Suite Finished (PHPUnit\TestFixture\Event\StubTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
