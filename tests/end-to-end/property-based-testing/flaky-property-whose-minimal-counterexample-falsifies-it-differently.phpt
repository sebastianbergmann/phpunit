--TEST--
A property whose minimal counterexample falsifies it in another way when it is replayed after shrinking than while it was shrunk is reported as flaky, together with the failure that was originally observed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/FailsDifferentlyAfterShrinkingTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\FailsDifferentlyAfterShrinkingTest::testFailsDifferentlyWhenItIsCalledWithOneHundredForTheSecondTime
PHPUnit\Framework\PropertyBased\FlakyPropertyException: The property is flaky: its minimal counterexample falsified it with LogicException at %sFailsDifferentlyAfterShrinkingTest.php:%d when it was replayed, but with RuntimeException at %sFailsDifferentlyAfterShrinkingTest.php:%d while it was shrunk, so its outcome does not depend on its arguments alone

Property falsified after %s:

    $value = 100;

Caused by
RuntimeException: large value

%sFailsDifferentlyAfterShrinkingTest.php:%d

ERRORS!
Tests: 1, Assertions: %d, Errors: 1.
