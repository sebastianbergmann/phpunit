--TEST--
GH-433: expectOutputString not completely working as expected
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue433Test';
$_SERVER['argv'][3] = dirname(__FILE__).'/433/Issue433Test.php';

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

..F

Time: %s, Memory: %sMb

There was 1 failure:

1) Issue433Test::testNotMatchingOutput
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'foo'
+'bar'


FAILURES!
Tests: 3, Assertions: 3, Failures: 1.

