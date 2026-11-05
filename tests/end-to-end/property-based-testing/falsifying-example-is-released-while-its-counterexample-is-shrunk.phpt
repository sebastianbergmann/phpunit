--TEST--
The example that falsifies a property is released before its counterexample is shrunk
--INI--
zend.exception_ignore_args=0
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/FalsifyingExampleIsReleasedWhileItsCounterexampleIsShrunkTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

FF                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\PropertyBasedTesting\FalsifyingExampleIsReleasedWhileItsCounterexampleIsShrunkTest::testFalsifiedByValuesGreaterThanTen
falsified

Property falsified after 5 examples, counterexample shrunk in %d steps:

    $value = 11;

%sFalsifyingExampleIsReleasedWhileItsCounterexampleIsShrunkTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\FalsifyingExampleIsReleasedWhileItsCounterexampleIsShrunkTest::testFalsifiedByEveryValue
falsified

Property falsified after 1 example, counterexample shrunk in 1 step:

    $value = 0;

%sFalsifyingExampleIsReleasedWhileItsCounterexampleIsShrunkTest.php:%d

FAILURES!
Tests: 2, Assertions: 8, Failures: 2.
