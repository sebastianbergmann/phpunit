--TEST--
Repeat option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'test2';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatWithFailuresTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

.FS                                                                 3 / 3 (100%)

Time: %s, Memory: %s MB

There was 1 failure:

1) RepeatWithFailuresTest::test2 (repeat attempt #2)
Failed asserting that true is false.

%s/tests/end-to-end/repeat/_files/RepeatWithFailuresTest.php:%d

FAILURES!
Tests: 3, Assertions: 2, Failures: 1, Skipped: 1.
