--TEST--
A property that is considered risky because too many of its examples were rejected states why they were rejected, the most frequent reason first, and how fewer examples can be rejected for it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/RejectionReasonsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

RR                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 risky tests:

1) PHPUnit\TestFixture\PropertyBasedTesting\RejectionReasonsTest::testRejectsEveryExampleForSeveralReasons
This property was checked for only 0 of 3 examples, 30 examples were rejected (%d by assume(), %d because no value that satisfies a filter was generated); generate the values that the property needs instead of rejecting the others

%sRejectionReasonsTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\RejectionReasonsTest::testRejectsItsOnlyExample
This property was checked for only 0 of 100 examples, 1 example was rejected by assume(); generate the values that the property needs instead of rejecting the others

%sRejectionReasonsTest.php:%d

OK, but there were issues!
Tests: 2, Assertions: 0, Risky: 2.
