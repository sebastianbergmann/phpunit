--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6142
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6142/Issue6142Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Issue6142\Issue6142Test::testOne
Failed asserting that '{"key": false}\n
' matches JSON string "{"key": true}
".
--- Expected
+++ Actual
@@ @@
 {
-    "key": true
+    "key": false
 }

%sIssue6142Test.php:%d

FAILURES!
Tests: 1, Assertions: 7, Failures: 1.
