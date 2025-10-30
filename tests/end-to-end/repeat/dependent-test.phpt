--TEST--
Repeat option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatDependentTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

....FSSS                                                            8 / 8 (100%)

Time: %s, Memory: %s MB

There was 1 failure:

1) RepeatDependentTest::test2
Failed asserting that false is true.

%s/tests/end-to-end/repeat/_files/RepeatDependentTest.php:%d

FAILURES!
Tests: 8, Assertions: 5, Failures: 1, Skipped: 3.
