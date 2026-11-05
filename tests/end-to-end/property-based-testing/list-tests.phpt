--TEST--
A property is listed as one test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--list-tests';
$_SERVER['argv'][] = __DIR__ . '/_files/RememberedCounterexampleTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available tests:
 - PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testValueIsNotMagic
 - PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testHolds
 - PHPUnit\TestFixture\PropertyBasedTesting\RememberedCounterexampleTest::testSumIsAtMostTen
