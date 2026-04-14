--TEST--
todo
--FILE--
<?php declare(strict_types=1);
$baseline = sys_get_temp_dir() . '/phpunit-test-baseline-' . uniqid() . '.xml';

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--generate-baseline';
$_SERVER['argv'][] = $baseline;
$_SERVER['argv'][] = '--ignore-baseline';
$_SERVER['argv'][] = __DIR__ . '/../../event/_files/SuccessTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

@unlink($baseline);
--EXPECTF--
%A
Test Runner Triggered PHPUnit Warning (Options --generate-baseline and --ignore-baseline cannot be used together)
%A
