--TEST--
The issues that are triggered by the final replay of the minimal counterexample of a property are reported for the property
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = __DIR__ . '/_files/IssueTriggeredByMinimalCounterexampleTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\IssueTriggeredByMinimalCounterexampleTest::testIsLessThanOneThousand
Failed asserting that 1000 is less than 1000.

Property falsified after 2 examples, counterexample shrunk in %d steps:

    $value = 1000;

%sIssueTriggeredByMinimalCounterexampleTest.php:%d

--

1 test triggered 1 deprecation:

1) %sIssueTriggeredByMinimalCounterexampleTest.php:%d
deprecation triggered by the minimal counterexample

FAILURES!
Tests: 1, Assertions: 2, Failures: 1, Deprecations: 1.
