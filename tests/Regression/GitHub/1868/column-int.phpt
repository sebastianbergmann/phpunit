--TEST--
#1868: Support --columns={int} option.
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = dirname(__FILE__) . '/options/ColumnTest.php';
$_SERVER['argv'][3] = '--columns=25';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.......... 10 / 20 ( 50%)
.......... 20 / 20 (100%)


Time: %s ms, Memory: %sMb

OK (20 tests, 20 assertions)
