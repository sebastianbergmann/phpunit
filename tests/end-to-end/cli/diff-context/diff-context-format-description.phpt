--TEST--
phpunit --diff-context with format description matching
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--diff-context=1';
$_SERVER['argv'][] = __DIR__ . '/_files/DiffContextTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\DiffContext\DiffContextTest::testMultiLineDiff
Failed asserting that string matches format description.
--- Expected
+++ Actual
@@ @@
 line09
-line10
+LINE10
 line11

%sDiffContextTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
