--TEST--
The tests that triggered an issue are grouped, sorted, and limited when --triggering-tests-limit is used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit.xml';
$_SERVER['argv'][] = '--triggering-tests-limit';
$_SERVER['argv'][] = '2';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

DDDDDDDD                                                            8 / 8 (100%)

Time: %s, Memory: %s

8 tests triggered 2 deprecations:

1) %sthird-party.php:%d
deprecation triggered in third-party code

Triggered 6 times (direct) by 5 tests in 2 test classes:

* PHPUnit\TestFixture\TriggeringTestsLimit\OneTest (3 tests, 4 times)
* PHPUnit\TestFixture\TriggeringTestsLimit\TwoTest (2 tests)

2) %sFirstParty.php:%d
deprecation triggered in first-party code

Triggered 3 times (self) by 3 tests:

* PHPUnit\TestFixture\TriggeringTestsLimit\OneTest::testFour (3 data sets)

OK, but there were issues!
Tests: 8, Assertions: 8, Deprecations: 2.
