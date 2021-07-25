--TEST--
#2137: Error message for invalid dataprovider
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/2137/Issue2137Test.php';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'BrandService';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) Error
The data provider specified for Issue2137Test::testBrandService is invalid.
Data set #0 is invalid.

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
