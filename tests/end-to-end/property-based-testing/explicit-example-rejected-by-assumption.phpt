--TEST--
An explicit example that is rejected by assume() is reported with a PHPUnit warning, and the other examples are checked
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ExplicitExampleRejectedByAssumptionTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 PHPUnit warning:

1) PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExampleRejectedByAssumptionTest::testRejectsNegativeValues
Explicit example #0 was rejected by assume()

%sExplicitExampleRejectedByAssumptionTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: %d, PHPUnit Warnings: 1.
