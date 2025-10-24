--TEST--
Repeat option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatWithErrorsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

FSS.FS..F...                                                      12 / 12 (100%)

Time: %s, Memory: %s MB

There were 3 failures:

1) RepeatWithErrorsTest::test1
Failed asserting that true is false.

%s/tests/end-to-end/repeat/_files/RepeatWithErrorsTest.php:%d

2) RepeatWithErrorsTest::test2
Failed asserting that true is false.

%s/tests/end-to-end/repeat/_files/RepeatWithErrorsTest.php:%d

3) RepeatWithErrorsTest::test3
Failed asserting that true is false.

%s/tests/end-to-end/repeat/_files/RepeatWithErrorsTest.php:%d

FAILURES!
Tests: 12, Assertions: 9, Failures: 3, Skipped: 3.
