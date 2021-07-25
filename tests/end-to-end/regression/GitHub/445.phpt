--TEST--
GH-455: expectOutputString not working in strict mode
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--disallow-test-output';
$_SERVER['argv'][] = __DIR__ . '/445/Issue445Test.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

..F                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) Issue445Test::testNotMatchingOutput
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'foo'
+'bar'

FAILURES!
Tests: 3, Assertions: 3, Failures: 1.
