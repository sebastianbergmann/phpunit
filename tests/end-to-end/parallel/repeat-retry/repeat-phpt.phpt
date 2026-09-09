--TEST--
phpunit --repeat 2 --parallel=2 runs the repetitions of a PHPT test one after another within the unit they form and skips the remaining ones after a failure
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatFailingPhpt.phpt';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

FS                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) %sRepeatFailingPhpt.phpt (repetition 1 of 2)
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'OK'
+'FAIL'

%sRepeatFailingPhpt.phpt:%d

FAILURES!
Tests: 2, Assertions: 1, Failures: 1, Skipped: 1.
