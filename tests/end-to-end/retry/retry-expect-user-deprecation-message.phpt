--TEST--
Expectations for deprecations of a retried test are verified against the deprecations triggered in the current attempt
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = __DIR__ . '/_files/ExpectUserDeprecationMessageTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.F                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\Retry\ExpectUserDeprecationMessageTest::testExpectationIsNotMetByDeprecationTriggeredInPreviousTest (attempt 2 of 2)
Expected deprecation with message "deprecation" was not triggered

FAILURES!
Tests: 2, Assertions: 2, Failures: 1.
