--TEST--
phpunit FailureTest ../_files/FailureTest.php SomethingElse
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'FailureTest';
$_SERVER['argv'][3] = dirname(dirname(__FILE__)) . '/_files/FailureTest.php';
$_SERVER['argv'][4] = 'SomethingElse';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

More than two positional arguments provided.

Usage: %s
