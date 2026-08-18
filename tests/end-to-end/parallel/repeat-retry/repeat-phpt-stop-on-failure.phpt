--TEST--
phpunit --repeat 2 --parallel=2 --stop-on-failure does not report the repetitions of a PHPT test that follow the failed one, which a sequential run would not have run either
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatFailingPhpt.phpt';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

F

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
Tests: 1, Assertions: 1, Failures: 1.
