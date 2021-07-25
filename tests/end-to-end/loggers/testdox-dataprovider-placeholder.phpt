--TEST--
phpunit --testdox --colors=always --verbose ../unit/Util/TestDox/ColorTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=always';
$_SERVER['argv'][] = '--verbose';
$_SERVER['argv'][] = realpath(__DIR__ . '/../../unit/Util/ColorTest.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF_EXTERNAL--
_files/raw_output_ColorTest.txt
