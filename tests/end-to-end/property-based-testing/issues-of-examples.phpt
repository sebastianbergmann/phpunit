--TEST--
The issues of the examples of a property are reported for the property, and whether a property performed assertions is decided on all of its examples
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = __DIR__ . '/_files/AggregatedIssuesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

R.D                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\AggregatedIssuesTest::testPerformsNoAssertions
This test did not perform any assertions

%sAggregatedIssuesTest.php:%d

--

1 test triggered 1 deprecation:

1) %sAggregatedIssuesTest.php:%d
deprecation

OK, but there were issues!
Tests: 3, Assertions: 11, Deprecations: 1, Risky: 1.
