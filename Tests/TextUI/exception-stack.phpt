--TEST--
phpunit ExceptionStackTest ../_files/ExceptionStack.php
--FILE--
<?php
define('PHPUNIT_TESTSUITE', TRUE);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'ExceptionStackTest';
$_SERVER['argv'][3] = dirname(dirname(__FILE__)) . '/_files/ExceptionStack.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

E

Time: %i %s, Memory: %sMb

There was 1 error:

1) ExceptionStackTest::testAssertArrayEqualsArray
ExceptionStackTestException: Child exception
message
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    0 => 1
+    0 => 2
 )

%s:%i

Caused by
%s:%i

FAILURES!
Tests: 1, Assertions: 1, Errors: 1.

