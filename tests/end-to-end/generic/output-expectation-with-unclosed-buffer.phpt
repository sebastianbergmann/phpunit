--TEST--
A test that sets an output expectation and leaves an output buffer open is reported as risky
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/OutputExpectationWithUnclosedBufferTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\OutputExpectationWithUnclosedBufferTest::testOutputExpectationIsReportedAsRiskyWhenBufferIsLeftOpen
* Test code or tested code did not close its own output buffers

* Test code or tested code closed output buffers other than its own

%sOutputExpectationWithUnclosedBufferTest.php:%i

OK, but there were issues!
Tests: 1, Assertions: 1, Notices: 1, Risky: 1.
