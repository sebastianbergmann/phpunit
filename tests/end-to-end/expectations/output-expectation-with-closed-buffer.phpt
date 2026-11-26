--TEST--
A test that sets an output expectation and closes the output buffer that is not its own is reported as risky
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/OutputExpectationWithClosedBufferTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\OutputExpectationWithClosedBufferTest::testOutputExpectationIsReportedAsRiskyWhenBufferIsClosed
Test code or tested code closed output buffers other than its own

%sOutputExpectationWithClosedBufferTest.php:%i

OK, but there were issues!
Tests: 1, Assertions: 1, Risky: 1.
