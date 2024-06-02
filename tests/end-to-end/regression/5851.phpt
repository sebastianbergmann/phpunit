--TEST--
https://github.com/sebastianbergmann/phpunit/pull/5861
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/5851/Issue5851Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

FFFIllegaly hide thisFFSneakyFNaughtySafeFFRRFRF.                                                    14 / 14 (100%)

Time: %s, Memory: %s

There were 10 failures:

1) PHPUnit\TestFixture\Issue5851Test::testInvalidFlushBuffer
PHPUnit\Framework\Exception: Test code or tested code flushed or cleaned global output buffers other than its own

2) PHPUnit\TestFixture\Issue5851Test::testInvalidSilencedFlushBuffer
PHPUnit\Framework\Exception: Test code or tested code flushed or cleaned global output buffers other than its own

3) PHPUnit\TestFixture\Issue5851Test::testInvalidFlushBufferEmpty
PHPUnit\Framework\Exception: Test code or tested code flushed or cleaned global output buffers other than its own

4) PHPUnit\TestFixture\Issue5851Test::testInvalidCleanExternalBuffer
PHPUnit\Framework\Exception: Test code or tested code flushed or cleaned global output buffers other than its own

5) PHPUnit\TestFixture\Issue5851Test::testRemovedAndAddedBufferNoOutput
PHPUnit\Framework\Exception: Test code or tested code first closed output buffers other than its own and later started output buffers it did not close

6) PHPUnit\TestFixture\Issue5851Test::testRemovedAndAddedBufferOutput
PHPUnit\Framework\Exception: Test code or tested code first closed output buffers other than its own and later started output buffers it did not close

7) PHPUnit\TestFixture\Issue5851Test::testRemovedAndAddedBufferExpectedOutput
PHPUnit\Framework\Exception: Test code or tested code first closed output buffers other than its own and later started output buffers it did not close

8) PHPUnit\TestFixture\Issue5851Test::testNonClosedBufferShouldntBeIgnored
Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
-''
+'Do not ignore thisor this'

9) PHPUnit\TestFixture\Issue5851Test::testNonRemovableBuffer
PHPUnit\Framework\Exception: Test code contains a non-removable output buffer - run test in separate process to avoid side-effects

10) PHPUnit\TestFixture\Issue5851Test::testNonRemovableBufferChunkSizeTooLow
PHPUnit\Framework\Exception: Tests with non-removable output buffer handlers must not call flush on them and the chunk size must be bigger than the expected output

--

There were 4 risky tests:

1) PHPUnit\TestFixture\Issue5851Test::testNonClosedBufferShouldntBeIgnored
Test code or tested code did not close its own output buffers

%s%eIssue5851Test.php:%i

2) PHPUnit\TestFixture\Issue5851Test::testNonClosedBufferShouldntBeIgnored2
Test code or tested code did not close its own output buffers

%s%eIssue5851Test.php:%i

3) PHPUnit\TestFixture\Issue5851Test::testNonRemovableBufferSeparateProcess
Non-removable output handler callback was not called, which could alter output

%s%eIssue5851Test.php:%i

4) PHPUnit\TestFixture\Issue5851Test::testNonRemovableBufferSeparateProcessAgain
Non-removable output handler callback was not called, which could alter output

%s%eIssue5851Test.php:%i

FAILURES!
Tests: 14, Assertions: 14, Failures: 10, Risky: 4.
