--TEST--
GH-1472: assertEqualXMLStructure modifies the tested elements
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue1472Test';
$_SERVER['argv'][3] = dirname(__FILE__) . '/1472/Issue1472Test.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
.

Time: %s, Memory: %sMb

OK (1 test, 4 assertions)
