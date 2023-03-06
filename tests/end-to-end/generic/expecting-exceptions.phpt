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

.F.F.F.F.F                                                        10 / 10 (100%)

Time: %s, Memory: %s

There were 5 failures:

1) PHPUnit\TestFixture\ExpectingExceptionsTest::testFailsWhenExpectedExceptionIsNotThrown
Failed asserting that exception of type "Exception" is thrown.

2) PHPUnit\TestFixture\ExpectingExceptionsTest::testFailsWhenExpectedExceptionIsThrownAndDoesNotHaveMessageThatIsOrContainsExpectedMessage
Failed asserting that exception message '' contains 'message'.

3) PHPUnit\TestFixture\ExpectingExceptionsTest::testFailsWhenExpectedExceptionIsThrownAndDoesNotHaveMessageThatMatchesRegularExpression
Failed asserting that exception message '' matches '/message/'.

4) PHPUnit\TestFixture\ExpectingExceptionsTest::testFailsWhenExpectedExceptionIsThrownAndDoesNotHaveExpectedCode
Failed asserting that 0 is equal to expected exception code 1234.

5) PHPUnit\TestFixture\ExpectingExceptionsTest::testFailsWhenExpectedExceptionObjectIsNotThrown
Failed asserting that exception of type "Exception" is thrown.

FAILURES!
Tests: 10, Assertions: 18, Failures: 5.
