--TEST--
phpunit --parallel=2 runs PHPT tests concurrently alongside worker test classes and forwards every result in global suite order
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

..F                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) %sbbb-failing.phpt
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'expected output'
+'actual output'

%sbbb-failing.phpt:7

FAILURES!
Tests: 3, Assertions: 3, Failures: 1.
