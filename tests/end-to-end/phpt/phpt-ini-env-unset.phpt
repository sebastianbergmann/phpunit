--TEST--
PHPT runner skips a test whose INI section references an environment variable that is not set
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-ini-env-unset.phpt';

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
Test Suite Started (%s%ephpt-ini-env-unset.phpt, 1 test)
Test Preparation Started (%s%ephpt-ini-env-unset.phpt)
Test Prepared (%s%ephpt-ini-env-unset.phpt)
Test Skipped (%s%ephpt-ini-env-unset.phpt)
Environment variable PHPT_ENVIRONMENT_VARIABLE_THAT_IS_NOT_SET is not set
Test Finished (%s%ephpt-ini-env-unset.phpt)
Test Suite Finished (%s%ephpt-ini-env-unset.phpt, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
