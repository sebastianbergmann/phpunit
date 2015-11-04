--TEST--
#1868: Support --version long option.
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--version';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.
