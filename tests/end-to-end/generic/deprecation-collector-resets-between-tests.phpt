--TEST--
Deprecation collector resets collected deprecations between tests
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/DeprecationCollectorResetTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.F                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\DeprecationCollector\DeprecationCollectorResetTest::testSecondDoesNotSeeFirstDeprecation
Expected deprecation with message "first deprecation" was not triggered

FAILURES!
Tests: 2, Assertions: 2, Failures: 1.
