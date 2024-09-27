--TEST--
phpunit ../../_files/DataProviderRequiresPhpUnitTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProviderRequiresPhpUnitTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

S..SS                                                               5 / 5 (100%)

Time: %s, Memory: %s

There were 3 skipped tests:

1) PHPUnit\TestFixture\DataProviderRequiresPhpUnitTest::testWithInvalidDataProvider
PHPUnit < 10 is required.

2) PHPUnit\TestFixture\DataProviderRequiresPhpUnitTest::testWithDataProviderThatThrows
PHPUnit < 10 is required.

3) PHPUnit\TestFixture\DataProviderRequiresPhpUnitTest::testWithDataProviderExternalThatThrows
PHPUnit < 10 is required.

OK, but some tests were skipped!
Tests: 5, Assertions: 2, Skipped: 3.

