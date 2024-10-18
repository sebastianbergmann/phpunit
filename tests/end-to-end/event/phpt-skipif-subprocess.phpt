--TEST--
The right events are emitted in the right order for a skipped PHPT test using a skipif subprocess
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-skipif-exit-subprocess.phpt';

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
Test Suite Started (%s%ephpt-skipif-exit-subprocess.phpt, 1 test)
Test Preparation Started (%s%ephpt-skipif-exit-subprocess.phpt)
Test Prepared (%s%ephpt-skipif-exit-subprocess.phpt)
Child Process Started
Child Process Finished
Test Skipped (%s%ephpt-skipif-exit-subprocess.phpt)
is test
Test Finished (%s%ephpt-skipif-exit-subprocess.phpt)
Test Suite Finished (%s%ephpt-skipif-exit-subprocess.phpt, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
