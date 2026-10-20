--TEST--
A property whose example that falsified it falsifies it again in only some of its replays states how often it was falsified again
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/PartiallyReproducedFailureTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\PartiallyReproducedFailureTest::testFailsForLargeValuesInEverySecondCall
large values fail in every second call
Failed asserting that %i is less than 100.

Property falsified after %s, counterexample shrunk in %s:

    $value = %i;

The example that falsified the property falsified it again in only 2 of 4 replays, so its outcome does not depend on its arguments alone.

%sPartiallyReproducedFailureTest.php:%d

FAILURES!
Tests: 1, Assertions: %d, Failures: 1.
