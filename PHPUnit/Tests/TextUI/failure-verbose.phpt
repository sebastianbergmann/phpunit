--TEST--
phpunit --verbose FailureTest ../../Samples/BankAccount/FailureTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--verbose';
$_SERVER['argv'][3] = 'FailureTest';
$_SERVER['argv'][4] = dirname(dirname(__FILE__)) . '/_files/FailureTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

FailureTest
FFFFFFFFF

Time: %i seconds

There were 9 failures:

1) testAssertArrayEqualsArray(FailureTest)
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ -1,5 +1,5 @@
 Array
 (
-    [0] => 1
+    [0] => 2
 )
 
%s/FailureTest.php:%i
%s/failure-verbose.php:%i

2) testAssertIntegerEqualsInteger(FailureTest)
Failed asserting that <integer:2> matches expected <integer:1>.
expected integer <1>
difference       <1>
got integer      <2>
%s/FailureTest.php:%i
%s/failure-verbose.php:%i

3) testAssertObjectEqualsObject(FailureTest)
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ -1,5 +1,5 @@
 stdClass Object
 (
-    [foo] => bar
+    [bar] => foo
 )
 
%s/FailureTest.php:%i
%s/failure-verbose.php:%i

4) testAssertNullEqualsString(FailureTest)
Failed asserting that <string:bar> matches expected <null>.

%s/FailureTest.php:%i
%s/failure-verbose.php:%i

5) testAssertStringEqualsString(FailureTest)
Failed asserting that two strings are equal.
expected string <foo>
difference      <xxx>
got string      <bar>
%s/FailureTest.php:%i
%s/failure-verbose.php:%i

6) testAssertTextEqualsText(FailureTest)
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ -1,3 +1,3 @@
 foo
-bar
+baz
 
%s/FailureTest.php:%i
%s/failure-verbose.php:%i

7) testAssertTextSameText(FailureTest)
expected string <foo>
difference      <xxx>
got string      <bar>
%s/FailureTest.php:%i
%s/failure-verbose.php:%i

8) testAssertObjectSameObject(FailureTest)
Failed asserting that two variables reference the same object.
%s/FailureTest.php:%i
%s/failure-verbose.php:%i

9) testAssertObjectSameNull(FailureTest)
<null> does not match expected type "object".
%s/FailureTest.php:%i
%s/failure-verbose.php:%i

FAILURES!
Tests: 9, Assertions: 9, Failures: 9.
