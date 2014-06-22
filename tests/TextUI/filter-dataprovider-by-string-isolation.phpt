--TEST--
phpunit --process-isolation --filter testFalse@false\ test DataProviderFilterTest ../_files/DataProviderFilterTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = '--filter';
$_SERVER['argv'][4] = 'testFalse@false test';
$_SERVER['argv'][5] = 'DataProviderFilterTest';
$_SERVER['argv'][6] = dirname(__FILE__).'/../_files/DataProviderFilterTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
   ___  __ _____  __  __     _ __
  / _ \/ // / _ \/ / / /__  (_) /_
 / ___/ _  / ___/ /_/ / _ \/ / __/
/_/  /_//_/_/   \____/_//_/_/\__/

PHPUnit %s by Sebastian Bergmann.

.

Time: %s, Memory: %sMb

OK (1 test, 1 assertion)
