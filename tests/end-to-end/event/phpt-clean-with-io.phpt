--TEST--
The right events are emitted in the right order for a PHPT test with a CLEAN section which pollutes the process
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-clean-with-io.phpt';

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
Test Suite Started (%sphpt-clean-with-io.phpt, 1 test)
Test Preparation Started (%sphpt-clean-with-io.phpt)
Test Prepared (%sphpt-clean-with-io.phpt)
Child Process Started
Child Process Finished
Test Passed (%sphpt-clean-with-io.phpt)
Test Finished (%sphpt-clean-with-io.phpt)
Test Suite Finished (%sphpt-clean-with-io.phpt, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
