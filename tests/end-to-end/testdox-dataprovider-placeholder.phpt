--TEST--
phpunit --testdox --colors=always --verbose RouterTest ../unit/Util/TestDox/ColorTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--testdox';
$_SERVER['argv'][3] = '--colors=always';
$_SERVER['argv'][4] = '--verbose';
$_SERVER['argv'][5] = __DIR__ . '/../unit/Util/TestDox/ColorTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF_EXTERNAL--
/../_files/raw_output_ColorTest.txt
