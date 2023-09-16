--TEST--
phpunit --no-output --log-events-verbose-text logfile.txt
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-verbose-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/../_files/log-events-text';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] PHPUnit Started (%s)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Configured
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Loaded (1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Event Facade Sealed
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Started
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Sorted
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Execution Started (1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Started (CLI Arguments, 1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Started (PHPUnit\TestFixture\LogEventsText\Test, 1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Preparation Started (PHPUnit\TestFixture\LogEventsText\Test::testOne)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Prepared (PHPUnit\TestFixture\LogEventsText\Test::testOne)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Assertion Failed (Constraint: is identical to an object of class "stdClass", Value: stdClass Object #%d ())
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Failed (PHPUnit\TestFixture\LogEventsText\Test::testOne)
                                                          Failed asserting that two variables reference the same object.
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Finished (PHPUnit\TestFixture\LogEventsText\Test::testOne)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Finished (PHPUnit\TestFixture\LogEventsText\Test, 1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Suite Finished (CLI Arguments, 1 test)
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Execution Finished
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] Test Runner Finished
[%s:%s:%s.%s / %s:%s:%s.%s] [%s bytes] PHPUnit Finished (Shell Exit Code: 1)
