--TEST--
The right events are emitted in the right order for a PHPT test using a subprocess via --INI--
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-ini-clean.phpt';

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
Test Suite Started (%s%ephpt-ini-clean.phpt, 1 test)
Test Preparation Started (%s%ephpt-ini-clean.phpt)
Test Prepared (%s%ephpt-ini-clean.phpt)
Child Process Started
Child Process Finished
Test Passed (%sphpt-ini-clean.phpt)
Child Process Started
Child Process Finished
Test Finished (%s%ephpt-ini-clean.phpt)
Test Suite Finished (%s%ephpt-ini-clean.phpt, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
