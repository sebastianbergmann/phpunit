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

SSD.                                                                4 / 4 (100%)

Time: %s, Memory: %s

There were 2 PHPUnit errors:

1) PHPUnit\TestFixture\DataProviderRequiresPhpUnitTest::testWithDataProviderThatThrows
The data provider specified for PHPUnit\TestFixture\DataProviderRequiresPhpUnitTest::testWithDataProviderThatThrows is invalid
Should have been skipped.

%s:%d

2) PHPUnit\TestFixture\DataProviderRequiresPhpUnitTest::testWithDataProviderExternalThatThrows
The data provider specified for PHPUnit\TestFixture\DataProviderRequiresPhpUnitTest::testWithDataProviderExternalThatThrows is invalid
Should have been skipped.

%s:%d

--

There were 2 skipped tests:

1) PHPUnit\TestFixture\DataProviderRequiresPhpUnitTest::testWithInvalidDataProvider with data set #0 (true)
PHPUnit < 10 is required.

2) PHPUnit\TestFixture\DataProviderRequiresPhpUnitTest::testWithInvalidDataProvider with data set #1 (true)
PHPUnit < 10 is required.

ERRORS!
Tests: 4, Assertions: 2, Errors: 2, PHPUnit Deprecations: 1, Skipped: 2.

