--TEST--
A custom implementation of ValueGenerator that is used for a property is reported as an error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/CustomValueGeneratorTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\CustomValueGeneratorTest::testUsesACustomImplementationOfValueGenerator
PHPUnit\Framework\PropertyBased\ValueGeneratorNotSupportedException: Class PHPUnit\TestFixture\PropertyBasedTesting\CustomValueGenerator implements PHPUnit\Framework\PropertyBased\ValueGenerator, which is not supported: generators have to be created and composed with the methods of PHPUnit\Framework\PropertyBased\Generators

ERRORS!
Tests: 1, Assertions: %d, Errors: 1.
