--TEST--
A data set that exhausts all attempts is reported as a regular failure while other data sets are retried independently
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderAllAttemptsFailTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

..F                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Retry\DataProviderAllAttemptsFailTest::testWithDataProvider@failing with data ('failing')
Failure for failing data set

%sDataProviderAllAttemptsFailTest.php:%d

--

There was 1 retried test:

1) PHPUnit\TestFixture\Retry\DataProviderAllAttemptsFailTest::testWithDataProvider#flaky
1 failed attempt

FAILURES!
Tests: 3, Assertions: 3, Failures: 1.
