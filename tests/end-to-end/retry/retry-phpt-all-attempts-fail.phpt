--TEST--
A PHPT test that fails every attempt is reported as a failure of the final attempt
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--retry';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = realpath(__DIR__ . '/_files/RetryFailingPhpt.phpt');

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) %s/RetryFailingPhpt.phpt (attempt 3 of 3)
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'OK'
+'FAIL'

%s/RetryFailingPhpt.phpt:7

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
