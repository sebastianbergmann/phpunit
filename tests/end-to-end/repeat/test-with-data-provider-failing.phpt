--TEST--
Repeat option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatWithDataProviderFailingTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

..FS..                                                              6 / 6 (100%)

Time: %s, Memory: %s MB

There was 1 failure:

1) RepeatWithDataProviderFailingTest::test1#1 with data (false)
Failed asserting that false is true.

%s/tests/end-to-end/repeat/_files/RepeatWithDataProviderFailingTest.php:%d

FAILURES!
Tests: 6, Assertions: 5, Failures: 1, Skipped: 1.
