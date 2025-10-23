--TEST--
Repeat option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatWithDataProviderAndDependsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

........FS..SS                                                    14 / 14 (100%)

Time: %s, Memory: %s MB

There was 1 failure:

1) RepeatWithDataProviderAndDependsTest::test2#1 with data (false)
Failed asserting that false is true.

%s/tests/end-to-end/repeat/_files/RepeatWithDataProviderAndDependsTest.php:%d

FAILURES!
Tests: 14, Assertions: 11, Failures: 1, Skipped: 3.
