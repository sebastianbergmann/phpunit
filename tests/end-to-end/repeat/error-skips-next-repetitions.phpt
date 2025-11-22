--TEST--
Repeat option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatWithFailuresTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

FSS.FS..F...                                                      12 / 12 (100%)

Time: %s, Memory: %s MB

There were 3 failures:

1) RepeatWithFailuresTest::test1
Failed asserting that true is false.

%s/tests/end-to-end/repeat/_files/RepeatWithFailuresTest.php:%d

2) RepeatWithFailuresTest::test2 (repeat attempt #2)
Failed asserting that true is false.

%s/tests/end-to-end/repeat/_files/RepeatWithFailuresTest.php:%d

3) RepeatWithFailuresTest::test3 (repeat attempt #3)
Failed asserting that true is false.

%s/tests/end-to-end/repeat/_files/RepeatWithFailuresTest.php:%d

FAILURES!
Tests: 12, Assertions: 9, Failures: 3, Skipped: 3.
