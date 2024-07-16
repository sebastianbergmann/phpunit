--TEST--
Details for skipped tests are displayed when --fail-on-skipped is used
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--fail-on-skipped';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/SkippedTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

S.                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 skipped test:

1) PHPUnit\TestFixture\TestRunnerStopping\SkippedTest::testOne
message

OK, but some tests were skipped!
Tests: 2, Assertions: 1, Skipped: 1.
