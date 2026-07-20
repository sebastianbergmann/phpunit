--TEST--
phpunit --repeat 3 --parallel=2 runs the repetitions of a test method inside the worker that runs its class and skips the remaining repetitions after a failure
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatFailureTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

.FS                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelRepeatRetry\RepeatFailureTest::testFailsOnSecondRepetition (repetition 2 of 3)
Failure on repetition 2

%sRepeatFailureTest.php:%d

FAILURES!
Tests: 3, Assertions: 2, Failures: 1, Skipped: 1.
