--TEST--
Only the tests that are affected by what changed cannot be run while the paths that changed are listed in a file
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--only-impacted';
$_SERVER['argv'][] = '--impacted-by-file';
$_SERVER['argv'][] = '-';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
%A
Test Runner Triggered PHPUnit Warning (Options --only-impacted and --impacted-by-file cannot be used together)
%A
