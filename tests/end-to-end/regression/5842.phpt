--TEST--
GH-5842: Failing output expectation must not skip tearDown and handler restoration
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/5842/Issue5842Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.F.                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Issue5842Test::testFailingOutputExpectation
Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
-'expected output'
+'actual output'

FAILURES!
Tests: 3, Assertions: 3, Failures: 1.
