--TEST--
#1868: Support bootstrap option.
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--bootstrap=' . __DIR__ . '/../../../bootstrap.php';
$_SERVER['argv'][3] = dirname(__FILE__) . '/options/BootstrapTest.php';

require __DIR__ . '/../../../../vendor/autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.

Time: %s, Memory: %sMb

OK (1 test, 1 assertion)
