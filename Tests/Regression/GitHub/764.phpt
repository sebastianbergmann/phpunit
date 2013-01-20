--TEST--
GH-764: "Not" constraints generate a confusing error message
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue764Test';
$_SERVER['argv'][3] = dirname(__FILE__).'/764/Issue764Test.php';

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

F

Time: %i %s, Memory: %sMb

There was 1 failure:

1) Issue764Test::testNotIsEqual
Failed asserting that 'username is required' does not contain "is required".


FAILURES!
Tests: 1, Assertions: 1, Failures: 1.

