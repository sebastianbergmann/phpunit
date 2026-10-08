--TEST--
A property for which fewer examples than configured could be checked because too many examples were rejected is considered risky
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/RejectedExamplesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\RejectedExamplesTest::testRejectsAlmostAllExamples
This property was checked for only 8 of 10 examples, 92 examples were rejected

%sRejectedExamplesTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 8, Risky: 1.
