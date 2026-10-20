--TEST--
The maximumTooLargeExamplesUntilHealthIsAssumed setting of the XML configuration file configures how many examples of a property can be rejected because they were too large before enough of them were checked
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/health-checks/phpunit.xml';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/TooLargeExamplesTest.php';

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

1) PHPUnit\TestFixture\PropertyBasedTesting\TooLargeExamplesTest::testGeneratesTooLargeExamples
This property was checked for only 0 of 100 examples because generating examples was stopped after 3 examples were rejected because generating them required too many choices while fewer than 10 examples were checked; generate smaller values

%sTooLargeExamplesTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 0, Risky: 1.
