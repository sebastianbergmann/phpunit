--TEST--
The tests that triggered an issue are all listed when --triggering-tests-limit 0 is used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-with-triggering-tests-limit.xml';
$_SERVER['argv'][] = '--triggering-tests-limit';
$_SERVER['argv'][] = '0';

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

Triggered by:

* PHPUnit\TestFixture\TriggeringTestsLimit\OneTest::testOne
  %sOneTest.php:%d

* PHPUnit\TestFixture\TriggeringTestsLimit\OneTest::testThree (2 times)
  %sOneTest.php:%d

* PHPUnit\TestFixture\TriggeringTestsLimit\OneTest::testTwo
  %sOneTest.php:%d

* PHPUnit\TestFixture\TriggeringTestsLimit\TwoTest::testOne
  %sTwoTest.php:%d

* PHPUnit\TestFixture\TriggeringTestsLimit\TwoTest::testTwo
  %sTwoTest.php:%d

2) %sFirstParty.php:%d
deprecation triggered in first-party code

Triggered by:

* PHPUnit\TestFixture\TriggeringTestsLimit\OneTest::testFour#0
  %sOneTest.php:%d

* PHPUnit\TestFixture\TriggeringTestsLimit\OneTest::testFour#1
  %sOneTest.php:%d

* PHPUnit\TestFixture\TriggeringTestsLimit\OneTest::testFour#2
  %sOneTest.php:%d

OK, but there were issues!
Tests: 8, Assertions: 8, Deprecations: 2.
