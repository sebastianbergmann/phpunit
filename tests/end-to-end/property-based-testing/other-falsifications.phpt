--TEST--
The other ways in which a property was falsified while its counterexample was shrunk are reported, each with the simplest counterexample that falsified it that way
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/OtherFalsificationsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

FF                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\PropertyBasedTesting\OtherFalsificationsTest::testIsFalsifiedInTwoWays
Failed asserting that 500 is less than 500.

Property falsified after %s, counterexample shrunk in %s:

    $value = 500;

Property also falsified by RuntimeException at %sOtherFalsificationsTest.php:%d with this counterexample:

    $value = %d;

%sOtherFalsificationsTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\OtherFalsificationsTest::testDrawsAValueThatFalsifiesItInTwoWays
Failed asserting that 200 is less than 200.

Property falsified after %s, counterexample shrunk in %s:

    Draw 1 (value): 200

Property also falsified by RuntimeException at %sOtherFalsificationsTest.php:%d with this counterexample:

    Draw 1 (value): %d

%sOtherFalsificationsTest.php:%d

FAILURES!
Tests: 2, Assertions: %d, Failures: 2.
