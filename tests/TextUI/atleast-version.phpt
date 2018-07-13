--TEST--
Support --atleast-version long option.
--FILE--
<?php
require __DIR__ . '/../bootstrap.php';

$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--atleast-version';
$_SERVER['argv'][] = '999';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
