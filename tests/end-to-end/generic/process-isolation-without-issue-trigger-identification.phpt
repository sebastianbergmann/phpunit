--TEST--
No source map is built for a child process when the identification of issue triggers is disabled and code coverage is not collected
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/process-isolation-without-issue-trigger-identification';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

D                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 deprecation:

1) %sprocess-isolation-without-issue-trigger-identification%esrc%eDeprecator.php:19
deprecation triggered by first-party code

Triggered by:

* PHPUnit\TestFixture\ProcessIsolationWithoutIssueTriggerIdentification\DeprecatorTest::testDeprecationTriggeredInSeparateProcessIsReported
  %sprocess-isolation-without-issue-trigger-identification%etests%eDeprecatorTest.php:20

OK, but there were issues!
Tests: 1, Assertions: 1, Deprecations: 1.
