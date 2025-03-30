--TEST--
#2137: Error message for invalid dataprovider
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/2137/Issue2137Test.php';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'BrandService';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

There were 2 PHPUnit errors:

1) PHPUnit\TestFixture\Issue2137Test::testBrandService
The data provider specified for PHPUnit\TestFixture\Issue2137Test::testBrandService is invalid
Data set #0 is invalid, expected array but got stdClass

%s:%d

2) PHPUnit\TestFixture\Issue2137Test::testSomethingElseInvalid
The data provider specified for PHPUnit\TestFixture\Issue2137Test::testSomethingElseInvalid is invalid
Data set #0 is invalid, expected array but got stdClass

%s:%d

--

There was 1 PHPUnit test runner warning:

1) No tests found in class "PHPUnit\TestFixture\Issue2137Test".

No tests executed!
