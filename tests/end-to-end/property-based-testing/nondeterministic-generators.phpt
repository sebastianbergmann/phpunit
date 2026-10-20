--TEST--
A PHPUnit warning is reported for a property whose generators do not generate the same values from the same choices, when the examples are generated or when the example that falsified the property is replayed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/NondeterministicGeneratorsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

WF                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\NondeterministicGeneratorsTest::testIsFalsifiedByListsWhoseSizeGrows
Failed asserting that actual size %d matches expected size 0.

Property falsified after %s with this counterexample:

    $values = [%s];

%sNondeterministicGeneratorsTest.php:%d

--

2 tests triggered 2 PHPUnit warnings:

1) PHPUnit\TestFixture\PropertyBasedTesting\NondeterministicGeneratorsTest::testDrawsFromARangeThatGrows
The generators of this property did not generate the same values from the same choices, so its examples may be generated more than once and its counterexamples may not be reproducible; values must not depend on anything but the choices they are generated from

%sNondeterministicGeneratorsTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\NondeterministicGeneratorsTest::testIsFalsifiedByListsWhoseSizeGrows
The generators of this property did not generate the same values from the same choices, so its examples may be generated more than once and its counterexamples may not be reproducible; values must not depend on anything but the choices they are generated from

%sNondeterministicGeneratorsTest.php:%d

FAILURES!
Tests: 2, Assertions: %d, Failures: 1, PHPUnit Warnings: 2.
