--TEST--
#1021: Depending on a test that uses a data provider does not work
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue1021Test';
$_SERVER['argv'][3] = dirname(__FILE__).'/1021/Issue1021Test.php';

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

OK (2 tests, 1 assertion)
