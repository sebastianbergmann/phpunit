--TEST--
phpunit --process-isolation ../../_files/OutputTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = __DIR__ . '/../../_files/OutputTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.F.F                                                                4 / 4 (100%)

Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\OutputTest::testExpectOutputStringFooActualBar
Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
-'foo'
+'bar'

2) PHPUnit\TestFixture\OutputTest::testExpectOutputRegexFooActualBar
Failed asserting that 'bar' matches PCRE pattern "/foo/".

FAILURES!
Tests: 4, Assertions: 4, Failures: 2.
