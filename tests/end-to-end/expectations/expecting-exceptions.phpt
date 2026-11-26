--TEST--
phpunit ../_files/ExpectingExceptionsTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/ExpectingExceptionsTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.F..FFF..FFF..FFF.FF                                              20 / 20 (100%)

Time: %s, Memory: %s

There were 12 failures:

1) PHPUnit\TestFixture\ExpectingExceptionsTest::test_expectException_and_expected_exception_is_not_thrown
Failed asserting that exception of type "Exception" is thrown.

2) PHPUnit\TestFixture\ExpectingExceptionsTest::test_expectException_and_expectExceptionMessage_and_expected_exception_is_thrown_but_does_not_have_expected_message
Failed asserting that exception message '' contains 'message'.

3) PHPUnit\TestFixture\ExpectingExceptionsTest::test_expectExceptionMessage_and_exception_is_thrown_but_does_not_have_expected_message
Failed asserting that exception message '' contains 'message'.

4) PHPUnit\TestFixture\ExpectingExceptionsTest::test_expectExceptionMessage_and_no_exception_is_thrown
Failed asserting that exception with message "message" is thrown

5) PHPUnit\TestFixture\ExpectingExceptionsTest::test_expectException_and_expectExceptionMessageMatches_and_expected_exception_is_thrown_but_does_not_have_expected_message
Failed asserting that exception message '' matches '/message/'.

6) PHPUnit\TestFixture\ExpectingExceptionsTest::test_expectExceptionMessageMatches_and_exception_is_thrown_but_does_not_have_expected_message
Failed asserting that exception message '' matches '/message/'.

7) PHPUnit\TestFixture\ExpectingExceptionsTest::test_expectExceptionMessageMatches_and_no_exception_is_thrown
Failed asserting that exception with message matching "/message/" is thrown

8) PHPUnit\TestFixture\ExpectingExceptionsTest::test_expectException_and_expectExceptionCode_and_expected_exception_is_thrown_but_does_not_have_expected_code
Failed asserting that 0 is equal to expected exception code 1234.

9) PHPUnit\TestFixture\ExpectingExceptionsTest::test_expectExceptionCode_and_exception_is_thrown_but_does_not_have_expected_code
Failed asserting that 0 is equal to expected exception code 1234.

10) PHPUnit\TestFixture\ExpectingExceptionsTest::test_expectExceptionCode_and_no_exception_is_thrown
Failed asserting that exception with code "1234" is thrown

11) PHPUnit\TestFixture\ExpectingExceptionsTest::test_expectExceptionObject_and_expected_exception_is_not_thrown
Failed asserting that 5678 is equal to expected exception code 1234.

12) PHPUnit\TestFixture\ExpectingExceptionsTest::test_expectExceptionObject_and_no_exception_is_thrown
Failed asserting that exception of type "Exception" is thrown.

FAILURES!
Tests: 20, Assertions: 30, Failures: 12.
