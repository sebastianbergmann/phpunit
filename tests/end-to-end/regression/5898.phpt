--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5898
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/5898/5898.phpt';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (%s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%s5898.phpt, 1 test)
Test Preparation Started (%s5898.phpt)
Test Prepared (%s5898.phpt)
Child Process Started
Child Process Finished
Test Passed (%s5898.phpt)
Test Finished (%s5898.phpt)
Test Suite Finished (%s5898.phpt, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
