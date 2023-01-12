--TEST--
GH-503: assertEquals() Line Ending Differences Are Obscure
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/503/Issue503Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Issue503Test::testCompareDifferentLineEndings
Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
 #Warning: Strings contain different line endings!
-'foo
+'foo
 '

%s:%i

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
