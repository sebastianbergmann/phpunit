--TEST--
The time zones that have an identifier are generated for parameters of type DateTimeZone
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/TimeZoneTypesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.F                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\TimeZoneTypesTest::testFalsifiedByTimeZone
Failed asserting that two strings are not identical.

Property falsified after %s:

    $value = new DateTimeZone(timezone: 'UTC');

%sTimeZoneTypesTest.php:%d

FAILURES!
Tests: 2, Assertions: %d, Failures: 1.
