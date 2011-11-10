--TEST--
GH-244: Expected Exception should support string codes
--FILE--
<?php
define('PHPUNIT_TESTSUITE', TRUE);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = 'Issue244Test';
$_SERVER['argv'][4] = dirname(__FILE__).'/244/Issue244Test.php';

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

.F

Time: %i %s, Memory: %sMb

There was 1 failure:

1) Issue244Test::testFails
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'OtherString'
+'123StringCode'

%s:%i

FAILURES!
Tests: 2, Assertions: 3, Failures: 1.
