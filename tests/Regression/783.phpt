--TEST--
#783: Tests getting executed twice when using multiple groups
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--group';
$_SERVER['argv'][3] = 'foo,bar';
$_SERVER['argv'][4] = 'ParentSuite';
$_SERVER['argv'][5] = dirname(__FILE__).'/783/ParentSuite.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
   ___  __ _____  __  __     _ __
  / _ \/ // / _ \/ / / /__  (_) /_
 / ___/ _  / ___/ /_/ / _ \/ / __/
/_/  /_//_/_/   \____/_//_/_/\__/

PHPUnit %s by Sebastian Bergmann.

..

Time: %s, Memory: %sMb

OK (2 tests, 0 assertions)
