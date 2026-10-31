--TEST--
When the examples of a property are no longer generated because too many of them were rejected, the message says how many of the generated examples and how many explicit examples were checked
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ExplicitExamplesOfPropertyThatRejectsItsGeneratedExamplesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExamplesOfPropertyThatRejectsItsGeneratedExamplesTest::testAcceptsOnlyItsExplicitExamples
This property was checked for only 0 of 10 generated examples (and 2 explicit examples) because generating examples was stopped after 50 examples were rejected by assume() while fewer than 10 examples were checked; generate the values that the property needs instead of rejecting the others

%sExplicitExamplesOfPropertyThatRejectsItsGeneratedExamplesTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 2, Risky: 1.
