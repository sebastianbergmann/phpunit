--TEST--
A test that leaves an output buffer open after output buffering was resumed at another output buffering level is reported as risky
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/ResumedOutputBufferingWithUnclosedBufferTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\ResumedOutputBufferingWithUnclosedBufferTest::testOne
Test code or tested code did not close its own output buffers

%sResumedOutputBufferingWithUnclosedBufferTest.php:%i

OK, but there were issues!
Tests: 1, Assertions: 1, Risky: 1.
