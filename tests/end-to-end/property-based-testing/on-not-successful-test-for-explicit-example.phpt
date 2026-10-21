--TEST--
onNotSuccessfulTest() is called for an explicit example that falsifies the property, because its failure is the one that is reported
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/OnNotSuccessfulTestForExplicitExampleTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

onNotSuccessfulTest() was called: Failed asserting that -1 is equal to 0 or is greater than 0.
F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\OnNotSuccessfulTestForExplicitExampleTest::testValueIsNotNegative
Failed asserting that -1 is equal to 0 or is greater than 0.

Property falsified by explicit example #0:

    $value = -1;

%sOnNotSuccessfulTestForExplicitExampleTest.php:%d

FAILURES!
Tests: 1, Assertions: 2, Failures: 1.
