--TEST--
DataProvider: duplicate string key across two providers
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/DuplicateKeyAcrossProvidersTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\DataProvider\DuplicateKeyAcrossProvidersTest::testOne
The data provider specified for PHPUnit\TestFixture\DataProvider\DuplicateKeyAcrossProvidersTest::testOne is invalid
The key "shared" has already been defined by provider PHPUnit\TestFixture\DataProvider\DuplicateKeyAcrossProvidersTest::providerA

%s:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\DataProvider\DuplicateKeyAcrossProvidersTest".

No tests executed!
