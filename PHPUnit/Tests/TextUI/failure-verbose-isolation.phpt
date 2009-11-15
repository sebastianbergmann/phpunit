--TEST--
phpunit --process-isolation --verbose FailureTest ../_files/FailureTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = '--verbose';
$_SERVER['argv'][4] = 'FailureTest';
$_SERVER['argv'][5] = dirname(dirname(__FILE__)) . '/_files/FailureTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

FailureTest
FFFFFFFFFFF

Time: %i %s

There were 11 failures:

1) FailureTest::testAssertArrayEqualsArray
message
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array
 (
-    [0] => 1
+    [0] => 2
 )

%s:%i

2) FailureTest::testAssertIntegerEqualsInteger
message
Failed asserting that <integer:2> matches expected <integer:1>.

%s:%i

3) FailureTest::testAssertObjectEqualsObject
message
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
 stdClass Object
 (
-    [foo] => bar
+    [bar] => foo
 )

%s:%i

4) FailureTest::testAssertNullEqualsString
message
Failed asserting that <string:bar> matches expected <null>.

%s:%i

5) FailureTest::testAssertStringEqualsString
message
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-foo
+bar

%s:%i

6) FailureTest::testAssertTextEqualsText
message
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
 foo
-bar
+baz

%s:%i

7) FailureTest::testAssertStringMatchesFormat
message
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-*%s*
+**

%s:%i

8) FailureTest::testAssertNumericEqualsNumeric
message
Failed asserting that <integer:2> matches expected <integer:1>.

%s:%i

9) FailureTest::testAssertTextSameText
message
--- Expected
+++ Actual
@@ @@
-foo
+bar

%s:%i

10) FailureTest::testAssertObjectSameObject
message
Failed asserting that two variables reference the same object.

%s:%i

11) FailureTest::testAssertObjectSameNull
message
<null> does not match expected type "object".

%s:%i

FAILURES!
Tests: 11, Assertions: 11, Failures: 11.
