--TEST--
phpunit ../_files/TestWithJsonAttributeAndDataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/TestWithJsonAttributeAndDataProviderTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

W                                                                   1 / 1 (100%)

Time: %s, Memory: %s

1 test triggered 1 PHPUnit warning:

1) PHPUnit\TestFixture\TestWithJsonAttributeAndDataProviderTest::testWithDifferentProviderTypes
Mixing #[DataProvider*] and #[TestWith*] attributes is not supported, only the data provided by #[DataProvider*] will be used

%sTestWithJsonAttributeAndDataProviderTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
