--TEST--
The issues that are triggered while the arguments for the examples of a property that are rejected are generated are reported for the property
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-all-issues';
$_SERVER['argv'][] = __DIR__ . '/_files/IssuesTriggeredWhileArgumentsOfRejectedExamplesAreGeneratedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

RN                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\IssuesTriggeredWhileArgumentsOfRejectedExamplesAreGeneratedTest::testClosurePassedToFilterTriggersADeprecationAndRejectsEveryValue
This property was checked for only 0 of 1 examples, 10 examples were rejected

%sIssuesTriggeredWhileArgumentsOfRejectedExamplesAreGeneratedTest.php:%d

--

1 test triggered 1 notice:

1) %sIssuesTriggeredWhileArgumentsOfRejectedExamplesAreGeneratedTest.php:%d
notice triggered for a value that is rejected by assume()

--

1 test triggered 1 deprecation:

1) %sIssuesTriggeredWhileArgumentsOfRejectedExamplesAreGeneratedTest.php:%d
deprecation triggered for a value that is rejected by filter()

OK, but there were issues!
Tests: 2, Assertions: 5, Deprecations: 1, Notices: 1, Risky: 1.
