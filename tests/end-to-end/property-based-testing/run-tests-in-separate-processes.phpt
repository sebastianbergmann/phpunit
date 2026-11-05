--TEST--
#[RunTestsInSeparateProcesses] does not apply to a property, and a warning is emitted for it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/RunTestsInSeparateProcessesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Class PHPUnit\TestFixture\PropertyBasedTesting\RunTestsInSeparateProcessesTest is annotated with #[RunTestsInSeparateProcesses] but PHPUnit\TestFixture\PropertyBasedTesting\RunTestsInSeparateProcessesTest::testIsDeclaredInAClassThatRunsTestsInSeparateProcesses is a property and will not be run in a separate process

OK, but there were issues!
Tests: 1, Assertions: 10, PHPUnit Warnings: 1.
