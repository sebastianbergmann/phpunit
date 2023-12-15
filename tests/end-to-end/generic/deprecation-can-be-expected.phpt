--TEST--
E_USER_DEPRECATED issues can be expected
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/TestForDeprecatedFeatureTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

..FF..FF                                                            8 / 8 (100%)

Time: %s, Memory: %s

There were 4 failures:

1) PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testExpectationOnExactDeprecationMessageWorksWhenExpectedDeprecationIsNotTriggered
Expected deprecation with message "message" was not triggered

2) PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testExpectationOnExactDeprecationMessageWorksWhenUnexpectedDeprecationIsTriggered
Expected deprecation with message "message" was not triggered

3) PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testExpectationOnDeprecationMessageMatchingRegularExpressionWorksWhenExpectedDeprecationIsNotTriggered
Expected deprecation with message matching regular expression "/message/" was not triggered

4) PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testExpectationOnDeprecationMessageMatchingRegularExpressionWorksWhenUnepectedDeprecationIsTriggered
Expected deprecation with message matching regular expression "/message/" was not triggered

FAILURES!
Tests: 8, Assertions: 10, Failures: 4.
