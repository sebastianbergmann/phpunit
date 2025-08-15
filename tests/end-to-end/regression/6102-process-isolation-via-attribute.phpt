--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6102
--XFAIL--
https://github.com/sebastianbergmann/phpunit/issues/6102
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../end-to-end/generic/_files/TestForDeprecatedFeatureInIsolationTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

..FF..FF                                                            8 / 8 (100%)

Time: %s, Memory: %s

There were 4 failures:

1) PHPUnit\TestFixture\Event\TestForDeprecatedFeatureInIsolationTest::testExpectationOnExactDeprecationMessageWorksWhenExpectedDeprecationIsNotTriggered
Expected deprecation with message "message" was not triggered

2) PHPUnit\TestFixture\Event\TestForDeprecatedFeatureInIsolationTest::testExpectationOnExactDeprecationMessageWorksWhenUnexpectedDeprecationIsTriggered
Expected deprecation with message "message" was not triggered

3) PHPUnit\TestFixture\Event\TestForDeprecatedFeatureInIsolationTest::testExpectationOnDeprecationMessageMatchingRegularExpressionWorksWhenExpectedDeprecationIsNotTriggered
Expected deprecation with message matching regular expression "/message/" was not triggered

4) PHPUnit\TestFixture\Event\TestForDeprecatedFeatureInIsolationTest::testExpectationOnDeprecationMessageMatchingRegularExpressionWorksWhenUnepectedDeprecationIsTriggered
Expected deprecation with message matching regular expression "/message/" was not triggered

FAILURES!
Tests: 8, Assertions: 10, Failures: 4.
