--TEST--
phpunit --strict IncompleteTest ../_files/IncompleteTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--strict';
$_SERVER['argv'][3] = 'IncompleteTest';
$_SERVER['argv'][4] = dirname(dirname(__FILE__)) . '/_files/IncompleteTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

I

Time: %s, Memory: %sMb

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 0, Incomplete: 1.
