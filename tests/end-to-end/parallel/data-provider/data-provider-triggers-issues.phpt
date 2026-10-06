--TEST--
phpunit --parallel=2 reports the deprecations, notices, and warnings that a data provider triggers when the worker invokes it for the tests it provides data for
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--display-notices';
$_SERVER['argv'][] = '--display-warnings';
$_SERVER['argv'][] = __DIR__ . '/_files/issues/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 warning:

1) %sIssuesTriggeredByDataProviderTest.php:25
warning in data provider

--

1 test triggered 1 notice:

1) %sIssuesTriggeredByDataProviderTest.php:24
notice in data provider

--

1 test triggered 1 deprecation:

1) %sIssuesTriggeredByDataProviderTest.php:23
deprecation in data provider

OK, but there were issues!
Tests: 1, Assertions: 1, Warnings: 1, Deprecations: 1, Notices: 1.
