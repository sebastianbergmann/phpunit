--TEST--
A PHPUnit warning is reported for a property whose generators do not generate the same values from the same choices
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

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 PHPUnit warning:

1) PHPUnit\TestFixture\PropertyBasedTesting\NondeterministicGeneratorsTest::testDrawsFromARangeThatGrows
The generators of this property did not generate the same values from the same choices, so its examples may be generated more than once and its counterexamples may not be reproducible; values must not depend on anything but the choices they are generated from

%sNondeterministicGeneratorsTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: %d, PHPUnit Warnings: 1.
