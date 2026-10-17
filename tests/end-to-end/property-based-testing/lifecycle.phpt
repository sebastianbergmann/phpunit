--TEST--
Every example has a fresh fixture, and expectations for test doubles and expected exceptions are verified for every example
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/LifecycleTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

..F                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\LifecycleTest::testExpectedExceptionIsExpectedForEveryExample
Failed asserting that exception of type "InvalidArgumentException" is thrown.

Property falsified after %s, counterexample shrunk in %s:

    $value = 1;

FAILURES!
Tests: 3, Assertions: %d, Failures: 1.
