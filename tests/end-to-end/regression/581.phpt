--TEST--
GH-581: PHPUnit_Util_Type::export adds extra newlines in Windows
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/581/Issue581Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Issue581Test::testExportingObjectsDoesNotBreakWindowsLineFeeds
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
     1 => 2
     2 => 'Test\r\n'
     3 => 4
-    4 => 5
+    4 => 1
     5 => 6
     6 => 7
     7 => 8
 )

%s:%i

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
