--TEST--
#1868: Support --columns=max option.
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = dirname(__FILE__) . '/options/ColumnTest.php';
$_SERVER['argv'][3] = '--columns=max';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

....................

Time: %s ms, Memory: %sMb

OK (20 tests, 20 assertions)
