--TEST--
Test impact data cannot be derived from the code coverage targets the tests declare while it is declined that it is recorded
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--do-not-record-test-impact-data';
$_SERVER['argv'][] = '--derive-test-impact-data-from-coverage-targets';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
%A
Test Runner Triggered PHPUnit Warning (Options --derive-test-impact-data-from-coverage-targets and --do-not-record-test-impact-data cannot be used together)
%A
