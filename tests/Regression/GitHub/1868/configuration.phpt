--TEST--
#1868: Support --configuration option.
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = dirname(__FILE__) . '/options/ConfigurationTest.php';
$_SERVER['argv'][3] = '--configuration=' . __DIR__ . '/options/configuration.xml';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.

Time: %s ms, Memory: %sMb

OK (1 test, 1 assertion)
