--TEST--
#1868: Support --exclude-group with one group.
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = dirname(__FILE__) . '/options/GroupTest.php';
$_SERVER['argv'][3] = '--exclude-group=group-2';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

..

Time: %s, Memory: %sMb

OK (2 tests, 2 assertions)
