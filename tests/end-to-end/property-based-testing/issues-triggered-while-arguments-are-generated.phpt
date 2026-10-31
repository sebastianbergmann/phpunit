--TEST--
The issues that are triggered while the arguments for the examples of a property are generated are reported for the property
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-all-issues';
$_SERVER['argv'][] = __DIR__ . '/_files/IssuesTriggeredWhileArgumentsAreGeneratedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

WDN                                                                 3 / 3 (100%)

Time: %s, Memory: %s

1 test triggered 1 warning:

1) %sClassWhoseConstructorTriggersAWarning.php:%d
warning triggered by constructor

Triggered by:

* PHPUnit\TestFixture\PropertyBasedTesting\IssuesTriggeredWhileArgumentsAreGeneratedTest::testConstructorOfParameterTriggersAWarning (5 times)
  %sIssuesTriggeredWhileArgumentsAreGeneratedTest.php:%d

--

1 test triggered 1 notice:

1) %sIssuesTriggeredWhileArgumentsAreGeneratedTest.php:%d
notice triggered by generator method

--

1 test triggered 1 deprecation:

1) %sIssuesTriggeredWhileArgumentsAreGeneratedTest.php:%d
deprecation triggered by closure passed to map()

OK, but there were issues!
Tests: 3, Assertions: 15, Warnings: 1, Deprecations: 1, Notices: 1.
