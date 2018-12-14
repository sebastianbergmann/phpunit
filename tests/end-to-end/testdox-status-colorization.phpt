--TEST--
phpunit --testdox --colors=always --verbose RouterTest ../_files/StatusTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--testdox';
$_SERVER['argv'][3] = '--colors=always';
$_SERVER['argv'][4] = '--verbose';
$_SERVER['argv'][5] = realpath(__DIR__ . '/../_files/StatusTest.php');

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF_EXTERNAL--
../_files/raw_output_StatusTest.txt
