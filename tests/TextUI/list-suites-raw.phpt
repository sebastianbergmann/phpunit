--TEST--
phpunit --list-suites-raw --configuration=__DIR__.'/../_files/configuration.suites.xml'
--FILE--
<?php
$_SERVER['argv'][1] = '--list-suites-raw';
$_SERVER['argv'][2] = '--configuration';
$_SERVER['argv'][3] = __DIR__.'/../_files/configuration.suites.xml';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
Suite One
Suite Two
