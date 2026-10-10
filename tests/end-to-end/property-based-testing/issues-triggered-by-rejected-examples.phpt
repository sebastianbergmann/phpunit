--TEST--
The issues that the examples of a property trigger before they are rejected by assume() are reported for the property
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-all-issues';
$_SERVER['argv'][] = __DIR__ . '/_files/IssuesTriggeredByRejectedExamplesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %sIssuesTriggeredByRejectedExamplesTest.php:%d
deprecation triggered for a value that is rejected by assume()

OK, but there were issues!
Tests: 1, Assertions: 5, Deprecations: 1.
