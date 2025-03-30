--TEST--
The right events are emitted in the right order for a PHPT test using a subprocess via --INI--
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-ini-subprocess.phpt';

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
Test Suite Started (%s%ephpt-ini-subprocess.phpt, 1 test)
Test Preparation Started (%s%ephpt-ini-subprocess.phpt)
Test Prepared (%s%ephpt-ini-subprocess.phpt)
Child Process Started
Child Process Finished
Child Process Started
Child Process Finished
Test Passed (%s%ephpt-ini-subprocess.phpt)
Test Finished (%s%ephpt-ini-subprocess.phpt)
Test Suite Finished (%s%ephpt-ini-subprocess.phpt, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
