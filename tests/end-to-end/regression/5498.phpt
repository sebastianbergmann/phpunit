--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5498
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/5498';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (%s)
Test Runner Configured
Test Suite Loaded (1 test)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (CLI Arguments, 1 test)
Test Suite Started (PHPUnit\TestFixture\Issue5498\Test, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Issue5498\Test::testOne)
Before Test Method Called (PHPUnit\TestFixture\Issue5498\Test::parentBefore)
Before Test Method Called (PHPUnit\TestFixture\Issue5498\Test::before)
Before Test Method Finished:
- PHPUnit\TestFixture\Issue5498\Test::parentBefore
- PHPUnit\TestFixture\Issue5498\Test::before
Test Prepared (PHPUnit\TestFixture\Issue5498\Test::testOne)
Test Passed (PHPUnit\TestFixture\Issue5498\Test::testOne)
After Test Method Called (PHPUnit\TestFixture\Issue5498\Test::after)
After Test Method Called (PHPUnit\TestFixture\Issue5498\Test::parentAfter)
After Test Method Finished:
- PHPUnit\TestFixture\Issue5498\Test::after
- PHPUnit\TestFixture\Issue5498\Test::parentAfter
Test Finished (PHPUnit\TestFixture\Issue5498\Test::testOne)
Test Suite Finished (PHPUnit\TestFixture\Issue5498\Test, 1 test)
Test Suite Finished (CLI Arguments, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
