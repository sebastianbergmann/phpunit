--TEST--
phpunit --strict NothingTest ../_files/NothingTest.php
--FILE--
<?php
define('PHPUNIT_TESTSUITE', TRUE);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--strict';
$_SERVER['argv'][3] = 'NothingTest';
$_SERVER['argv'][4] = dirname(dirname(__FILE__)) . '/_files/NothingTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

I

Time: %s, Memory: %sMb

OK, but incomplete or skipped tests!
Tests: 1, Assertions: 0, Incomplete: 1.
