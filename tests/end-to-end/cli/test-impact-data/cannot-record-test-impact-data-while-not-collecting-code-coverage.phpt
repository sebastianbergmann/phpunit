--TEST--
Test impact data cannot be recorded while no code coverage is collected
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--no-coverage';
$_SERVER['argv'][] = '--record-test-impact-data';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
%A
Test Runner Triggered PHPUnit Warning (Options --no-coverage and --record-test-impact-data cannot be used together)
%A
