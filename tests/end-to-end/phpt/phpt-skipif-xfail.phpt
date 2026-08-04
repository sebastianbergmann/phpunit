--TEST--
PHPT runner treats a SKIPIF section that prints "xfail <reason>" as an XFAIL section
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-incomplete';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-skipif-xfail.phpt';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

I                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 incomplete test:

1) %sphpt-skipif-xfail.phpt
this feature is known to be broken

Caused by
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'expected output'
+'actual output'

OK, but there were issues!
Tests: 1, Assertions: 1, Incomplete: 1.
