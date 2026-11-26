--TEST--
phpunit ../../_files/CwdRestoreTest.php
--FILE--
<?php declare(strict_types=1);
$cwd = getcwd();

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = __DIR__ . '/../../_files/CwdRestoreTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

var_dump($cwd === getcwd());
--EXPECTF--
bool(true)
