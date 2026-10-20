--TEST--
With 0 for the examplesCheckedUntilHealthIsAssumed setting of the XML configuration file, examples of a property are generated regardless of how many of them were rejected
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/health-checks-disabled/phpunit.xml';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/RejectedExamplesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s
Random Seed:   1234

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\RejectedExamplesTest::testRejectsAlmostAllExamples
This property was checked for only 1 of 10 examples, 99 examples were rejected by assume(); generate the values that the property needs instead of rejecting the others

%sRejectedExamplesTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, Risky: 1.
