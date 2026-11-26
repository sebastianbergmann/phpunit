--TEST--
phpunit ../../_files/ExceptionStackTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-report-useless-tests';
$_SERVER['argv'][] = __DIR__ . '/../../_files/ExceptionStackTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

EE                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 errors:

1) PHPUnit\TestFixture\ExceptionStackTest::testPrintingChildException
PHPUnit\Framework\Exception: Child exception
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

2) PHPUnit\TestFixture\ExceptionStackTest::testNestedExceptions
Exception: One

%s:%i

Caused by
InvalidArgumentException: Two

%s:%i

Caused by
Exception: Three

%s:%i

ERRORS!
Tests: 2, Assertions: 1, Errors: 2.
