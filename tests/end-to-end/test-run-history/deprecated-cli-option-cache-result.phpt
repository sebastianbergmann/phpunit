--TEST--
The --cache-result CLI option is deprecated
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--cache-result';
$_SERVER['argv'][] = __DIR__ . '/../event/_files/SuccessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
%A
Test Runner Triggered PHPUnit Deprecation (The "--cache-result" CLI option is deprecated and will be removed in PHPUnit 14. Use "--record-test-run-history" instead.)
%A
