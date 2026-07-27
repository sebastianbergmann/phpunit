--TEST--
todo
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--cache-test-index';
$_SERVER['argv'][] = '--do-not-cache-test-index';
$_SERVER['argv'][] = __DIR__ . '/../../event/_files/SuccessTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
%A
Test Runner Triggered PHPUnit Warning (Options --cache-test-index and --do-not-cache-test-index cannot be used together)
%A
