--TEST--
The issues that are triggered by a generator method that does not return a value generator are reported for the property, together with the error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-warnings';
$_SERVER['argv'][] = __DIR__ . '/_files/IssueTriggeredByGeneratorMethodThatDoesNotReturnAValueGeneratorTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredByGeneratorMethodThatDoesNotReturnAValueGeneratorTest::testGeneratorMethodTriggersAWarning
PHPUnit\Framework\PropertyBased\InvalidPropertyException: Property PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredByGeneratorMethodThatDoesNotReturnAValueGeneratorTest::testGeneratorMethodTriggersAWarning cannot be checked: PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredByGeneratorMethodThatDoesNotReturnAValueGeneratorTest::integers() does not return a PHPUnit\Framework\PropertyBased\ValueGenerator

--

1 test triggered 1 warning:

1) %sIssueTriggeredByGeneratorMethodThatDoesNotReturnAValueGeneratorTest.php:%d
warning triggered by generator method

ERRORS!
Tests: 1, Assertions: 0, Errors: 1, Warnings: 1.
