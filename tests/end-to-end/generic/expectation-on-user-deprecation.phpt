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

.F.F                                                                4 / 4 (100%)

Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testTwo
Expected deprecation with message "message" was not triggered

2) PHPUnit\TestFixture\Event\TestForDeprecatedFeatureTest::testFour
Expected deprecation with message matching regular expression "message" was not triggered

FAILURES!
Tests: 4, Assertions: 4, Failures: 2.
