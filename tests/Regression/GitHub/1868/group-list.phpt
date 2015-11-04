--TEST--
#1868: Support --list-groups switch.
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = dirname(__FILE__) . '/options/GroupTest.php';
$_SERVER['argv'][3] = '--list-groups';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test group(s):
 - default
 - group-1
 - group-2
