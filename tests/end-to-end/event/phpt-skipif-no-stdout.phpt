--TEST--
The right events are emitted in the right order for a PHPT test with --SKIPIF-- that has no standard output side-effect
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-skipif-no-stdout.phpt';

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
Test Suite Started (%s%ephpt-skipif-no-stdout.phpt, 1 test)
Test Preparation Started (%s%ephpt-skipif-no-stdout.phpt)
Test Prepared (%s%ephpt-skipif-no-stdout.phpt)
Child Process Started
Child Process Finished
Test Considered Risky (%s%ephpt-skipif-no-stdout.phpt)
SKIPIF section does not produce output that could result in the test being skipped
Child Process Started
Child Process Finished
Test Passed (%s%ephpt-skipif-no-stdout.phpt)
Test Finished (%s%ephpt-skipif-no-stdout.phpt)
Test Suite Finished (%s%ephpt-skipif-no-stdout.phpt, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
