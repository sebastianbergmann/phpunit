--TEST--
todo
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--generate-baseline';
$_SERVER['argv'][] = '/tmp/baseline.xml';
$_SERVER['argv'][] = '--use-baseline';
$_SERVER['argv'][] = '/tmp/baseline.xml';
$_SERVER['argv'][] = __DIR__ . '/../../event/_files/SuccessTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
%A
Test Runner Triggered PHPUnit Warning (Options --generate-baseline and --use-baseline cannot be used together)
%A
