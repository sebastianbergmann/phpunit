--TEST--
PHPT runner strips the dash separator from a SKIPIF message
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-skipif-message-with-dash.phpt';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%s%ephpt-skipif-message-with-dash.phpt, 1 test)
Test Preparation Started (%s%ephpt-skipif-message-with-dash.phpt)
Test Prepared (%s%ephpt-skipif-message-with-dash.phpt)
Test Skipped (%s%ephpt-skipif-message-with-dash.phpt)
only for demonstration purposes
Test Finished (%s%ephpt-skipif-message-with-dash.phpt)
Test Suite Finished (%s%ephpt-skipif-message-with-dash.phpt, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
