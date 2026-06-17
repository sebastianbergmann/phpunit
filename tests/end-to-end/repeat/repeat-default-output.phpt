--TEST--
--repeat reports each repetition with its repetition number in the default progress and result output
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/_files/FailureOnSecondRepetitionTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.F                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Repeat\FailureOnSecondRepetitionTest::testOne (repetition 2 of 2)
Failure on second repetition

%sFailureOnSecondRepetitionTest.php:%d

FAILURES!
Tests: 2, Assertions: 2, Failures: 1.
