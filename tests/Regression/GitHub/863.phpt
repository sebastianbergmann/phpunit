--TEST--
GH-863: Number of tests to run calculated incorrectly when --repeat is used
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--repeat';
$_SERVER['argv'][3] = '50';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = dirname(dirname(dirname(__FILE__))) . '/_files/BankAccountTest.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
   ___  __ _____  __  __     _ __
  / _ \/ // / _ \/ / / /__  (_) /_
 / ___/ _  / ___/ /_/ / _ \/ / __/
/_/  /_//_/_/   \____/_//_/_/\__/

PHPUnit %s by Sebastian Bergmann.

...............................................................  63 / 150 ( 42%)
............................................................... 126 / 150 ( 84%)
........................

Time: %s, Memory: %sMb

OK (150 tests, 150 assertions)
