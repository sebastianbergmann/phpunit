--TEST--
DataProvider: provider method is also a test method triggers warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/IsAlsoTestMethodTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

R.                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Method PHPUnit\TestFixture\DataProvider\IsAlsoTestMethodTest::testProvider() used by test method PHPUnit\TestFixture\DataProvider\IsAlsoTestMethodTest::testOne() is also a test method

--

There was 1 risky test:

1) PHPUnit\TestFixture\DataProvider\IsAlsoTestMethodTest::testProvider
This test did not perform any assertions

%s:%d

OK, but there were issues!
Tests: 2, Assertions: 1, PHPUnit Warnings: 1, Risky: 1.
