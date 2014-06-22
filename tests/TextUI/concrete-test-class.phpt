--TEST--
phpunit ConcreteTest ../_files/ConcreteTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'ConcreteTest';
$_SERVER['argv'][3] = dirname(dirname(__FILE__)) . '/_files/ConcreteTest.php';

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
