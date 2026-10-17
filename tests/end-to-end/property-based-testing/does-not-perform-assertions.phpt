--TEST--
A property with #[DoesNotPerformAssertions] is not considered risky when its examples perform no assertions, and it is considered risky with the number of assertions of all of its examples when they perform assertions
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/DoesNotPerformAssertionsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.R                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\DoesNotPerformAssertionsTest::testPerformsAnAssertionForEveryExample
This test is not expected to perform assertions but performed 10 assertions

%sDoesNotPerformAssertionsTest.php:%d

OK, but there were issues!
Tests: 2, Assertions: 10, Risky: 1.
