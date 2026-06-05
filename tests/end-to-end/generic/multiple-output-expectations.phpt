--TEST--
expectOutputString() and expectOutputRegex() can be combined and repeated; conflicting exact strings trigger a PHPUnit warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/MultipleOutputExpectationsTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.F                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\MultipleOutputExpectationsTest::testConflictingStringExpectationsTriggerWarning
Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
-'bar'
+'foo'

--

1 test triggered 1 PHPUnit warning:

1) PHPUnit\TestFixture\MultipleOutputExpectationsTest::testConflictingStringExpectationsTriggerWarning
Output cannot be expected to be identical to more than one string; expectOutputString() was already called with a different argument

%s%eMultipleOutputExpectationsTest.php:27

FAILURES!
Tests: 2, Assertions: 5, Failures: 1, PHPUnit Warnings: 1.
