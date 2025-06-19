--TEST--
todo
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--do-not-fail-on-phpunit-deprecation';
$_SERVER['argv'][] = '--fail-on-phpunit-deprecation';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/DeprecationTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
%A
Test Runner Triggered Warning (Options --fail-on-phpunit-deprecation and --do-not-fail-on-phpunit-deprecation cannot be used together)
%A
