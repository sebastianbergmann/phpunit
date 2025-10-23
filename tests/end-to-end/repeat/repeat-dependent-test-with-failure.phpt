--TEST--
Repeat option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/_files/DependentOfTestFailedInRepetitionTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

.FSS                                                                4 / 4 (100%)

Time: %s, Memory: %s MB

There was 1 failure:

1) DependentOfTestFailedInRepetitionTest::test1
Failed asserting that true is false.

%s/tests/end-to-end/repeat/_files/DependentOfTestFailedInRepetitionTest.php:%d

FAILURES!
Tests: 4, Assertions: 2, Failures: 1, Skipped: 2.
