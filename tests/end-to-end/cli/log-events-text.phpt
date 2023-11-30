--TEST--
phpunit --no-output --log-events-text logfile.txt
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/../_files/log-events-text';

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
Test Suite Started (CLI Arguments, 1 test)
Test Suite Started (PHPUnit\TestFixture\LogEventsText\Test, 1 test)
Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testOne)
Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testOne)
Assertion Failed (Constraint: is identical to an object of class "stdClass", Value: {enable export of objects to see this value})
Test Failed (PHPUnit\TestFixture\LogEventsText\Test::testOne)
Failed asserting that two variables reference the same object.
Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testOne)
Test Suite Finished (PHPUnit\TestFixture\LogEventsText\Test, 1 test)
Test Suite Finished (CLI Arguments, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
