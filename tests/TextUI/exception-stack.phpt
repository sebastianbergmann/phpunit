--TEST--
phpunit ExceptionStackTest ../_files/ExceptionStackTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'ExceptionStackTest';
$_SERVER['argv'][3] = dirname(dirname(__FILE__)) . '/_files/ExceptionStackTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
   ___  __ _____  __  __     _ __
  / _ \/ // / _ \/ / / /__  (_) /_
 / ___/ _  / ___/ /_/ / _ \/ / __/
/_/  /_//_/_/   \____/_//_/_/\__/

PHPUnit %s by Sebastian Bergmann.

EE

Time: %s, Memory: %sMb

There were 2 errors:

1) ExceptionStackTest::testPrintingChildException
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
%s:%i

Caused by
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

2) ExceptionStackTest::testNestedExceptions
Exception: One

%s:%i

Caused by
InvalidArgumentException: Two

%s:%i

Caused by
Exception: Three

%s:%i

FAILURES!
Tests: 2, Assertions: 1, Errors: 2.
