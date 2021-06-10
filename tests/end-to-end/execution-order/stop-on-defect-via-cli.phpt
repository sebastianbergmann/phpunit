--TEST--
phpunit --stop-on-defect ./tests/_files/FailureTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--stop-on-defect';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/FailureTest.php');

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

F

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\FailureTest::testAssertArrayEqualsArray
message
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    0 => 1
+    0 => 2
 )

%sFailureTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
