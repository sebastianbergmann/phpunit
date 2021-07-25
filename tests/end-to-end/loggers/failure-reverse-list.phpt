--TEST--
phpunit --reverse-list ../../_files/FailureTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--reverse-list';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/FailureTest.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

FFFFFFFFFFFFF                                                     13 / 13 (100%)

Time: %s, Memory: %s

There were 13 failures:

1) PHPUnit\TestFixture\FailureTest::testAssertStringMatchesFormatFile
Failed asserting that string matches format description.
--- Expected
+++ Actual
@@ @@
-FOO
+...BAR...

%s:%d

2) PHPUnit\TestFixture\FailureTest::testAssertFloatSameFloat
message
Failed asserting that 1.5 is identical to 1.0.

%s:%d

3) PHPUnit\TestFixture\FailureTest::testAssertObjectSameNull
message
Failed asserting that null is identical to an object of class "stdClass".

%s:%d

4) PHPUnit\TestFixture\FailureTest::testAssertObjectSameObject
message
Failed asserting that two variables reference the same object.

%s:%d

5) PHPUnit\TestFixture\FailureTest::testAssertTextSameText
message
Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
-'foo'
+'bar'

%s:%d

6) PHPUnit\TestFixture\FailureTest::testAssertNumericEqualsNumeric
message
Failed asserting that 2 matches expected 1.

%s:%d

7) PHPUnit\TestFixture\FailureTest::testAssertStringMatchesFormat
message
Failed asserting that string matches format description.
--- Expected
+++ Actual
@@ @@
-*%s*
+**

%s:%d

8) PHPUnit\TestFixture\FailureTest::testAssertTextEqualsText
message
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
 'foo\n
-bar\n
+baz\n
 '

%s:%d

9) PHPUnit\TestFixture\FailureTest::testAssertStringEqualsString
message
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'foo'
+'bar'

%s:%d

10) PHPUnit\TestFixture\FailureTest::testAssertNullEqualsString
message
Failed asserting that 'bar' matches expected null.

%s:%d

11) PHPUnit\TestFixture\FailureTest::testAssertObjectEqualsObject
message
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
 stdClass Object (
-    'foo' => 'bar'
+    'bar' => 'foo'
 )

%s:%d

12) PHPUnit\TestFixture\FailureTest::testAssertIntegerEqualsInteger
message
Failed asserting that 2 matches expected 1.

%s:%d

13) PHPUnit\TestFixture\FailureTest::testAssertArrayEqualsArray
message
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    0 => 1
+    0 => 2
 )

%s:%d

FAILURES!
Tests: 13, Assertions: 14, Failures: 13.
