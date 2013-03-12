--TEST--
phpunit --strict --process-isolation IncompleteTest ../_files/IncompleteTest.php
--FILE--
<?php
define('PHPUNIT_TESTSUITE', TRUE);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--strict';
$_SERVER['argv'][3] = '--process-isolation';
$_SERVER['argv'][4] = 'NothingTest';
$_SERVER['argv'][5] = dirname(dirname(__FILE__)) . '/_files/NothingTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

I

Time: %s, Memory: %sMb

OK, but incomplete or skipped tests!
Tests: 1, Assertions: 0, Incomplete: 1.
