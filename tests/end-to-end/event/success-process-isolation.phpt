--TEST--
The right events are emitted in the right order for a successful test that is run in an isolated process
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/SuccessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\SuccessTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\SuccessTest::testSuccess)
Test Prepared (PHPUnit\TestFixture\Event\SuccessTest::testSuccess)
Test Passed (PHPUnit\TestFixture\Event\SuccessTest::testSuccess)
Test Finished (PHPUnit\TestFixture\Event\SuccessTest::testSuccess)
Test Suite Finished (PHPUnit\TestFixture\Event\SuccessTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
