--TEST--
A property that targets how close its examples come to the rare values that falsify it is falsified, while the same property without target() is not
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-property-statistics';
$_SERVER['argv'][] = __DIR__ . '/_files/TargetedScoresTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

F.                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\TargetedScoresTest::testFindsTheRareValuesThatFalsifyItByTargetingHowCloseValuesComeToThem
Failed asserting that true is false.

Property falsified after 128 examples, counterexample shrunk in 5 steps:

    $value = 1000000;

%sTargetedScoresTest.php:%d

--

Statistics of 2 properties:

1) PHPUnit\TestFixture\PropertyBasedTesting\TargetedScoresTest::testFindsTheRareValuesThatFalsifyItByTargetingHowCloseValuesComeToThem
128 examples checked, 0 rejected

    closeness: highest targeted score -50

2) PHPUnit\TestFixture\PropertyBasedTesting\TargetedScoresTest::testDoesNotFindTheRareValuesThatFalsifyItWithoutTargetingThem
200 examples checked, 0 rejected

FAILURES!
Tests: 2, Assertions: %d, Failures: 1.
