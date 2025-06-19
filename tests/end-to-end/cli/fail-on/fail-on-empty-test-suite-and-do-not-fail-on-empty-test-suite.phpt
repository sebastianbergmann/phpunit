--TEST--
todo
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'doesNotExist';
$_SERVER['argv'][] = '--fail-on-empty-test-suite';
$_SERVER['argv'][] = '--do-not-fail-on-empty-test-suite';
$_SERVER['argv'][] = __DIR__ . '/../../event/_files/SuccessTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
%A
Test Runner Triggered Warning (Options --do-not-fail-on-empty-test-suite and --fail-on-empty-test-suite cannot be used together)
%A
