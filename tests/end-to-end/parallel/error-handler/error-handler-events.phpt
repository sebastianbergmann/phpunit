--TEST--
phpunit --parallel=2 reports the deprecations and warnings the error handler records in a worker process
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--display-deprecations';
$_SERVER['argv'][] = '--display-warnings';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

DWDW                                                                4 / 4 (100%)

Time: %s, Memory: %s

2 tests triggered 2 warnings:

1) %sFirstIssueTest.php:28
warning in FirstIssueTest

2) %sSecondIssueTest.php:28
warning in SecondIssueTest

--

2 tests triggered 2 deprecations:

1) %sFirstIssueTest.php:21
deprecation in FirstIssueTest

2) %sSecondIssueTest.php:21
deprecation in SecondIssueTest

OK, but there were issues!
Tests: 4, Assertions: 4, Warnings: 2, Deprecations: 2.
