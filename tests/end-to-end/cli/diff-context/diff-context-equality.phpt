--TEST--
phpunit --diff-context with assertEquals
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--diff-context=1';
$_SERVER['argv'][] = __DIR__ . '/_files/EqualityDiffContextTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\DiffContext\EqualityDiffContextTest::testArrayEquality
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
     'key07' => 'val07'
-    'key08' => 'val08'
+    'key08' => 'CHANGED'
     'key09' => 'val09'

%sEqualityDiffContextTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
