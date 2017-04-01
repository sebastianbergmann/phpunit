--TEST--
#2448: Weird error when trying to run `Test` from `Test.php` but `Test.php` does not exist
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Test';

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
Cannot open file "Test.php".
