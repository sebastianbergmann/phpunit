--TEST--
The issues with the code coverage metadata of a property that is falsified are reported once
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/FalsifiedPropertyWithDuplicateCoverageTargetTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyWithDuplicateCoverageTargetTest::testIsLessThanOneThousand
Failed asserting that 1000 is less than 1000.

Property falsified after 2 examples, counterexample shrunk in %d steps:

    $value = 1000;

%sFalsifiedPropertyWithDuplicateCoverageTargetTest.php:%d

--

1 test triggered 1 PHPUnit warning:

1) PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyWithDuplicateCoverageTargetTest::testIsLessThanOneThousand
Class PHPUnit\TestFixture\PropertyBasedTesting\Money is targeted multiple times by the same "Covers" attribute

%sFalsifiedPropertyWithDuplicateCoverageTargetTest.php:%d

FAILURES!
Tests: 1, Assertions: 2, Failures: 1, PHPUnit Warnings: 1.
