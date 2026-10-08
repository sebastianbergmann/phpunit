--TEST--
A property whose generator method does not return a ValueGenerator is reported as an error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/GeneratorMethodThatDoesNotReturnAValueGeneratorTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\GeneratorMethodThatDoesNotReturnAValueGeneratorTest::testUsesAGeneratorMethodThatDoesNotReturnAValueGenerator
PHPUnit\Framework\PropertyBased\InvalidPropertyException: Property PHPUnit\TestFixture\PropertyBasedTesting\GeneratorMethodThatDoesNotReturnAValueGeneratorTest::testUsesAGeneratorMethodThatDoesNotReturnAValueGenerator cannot be checked: PHPUnit\TestFixture\PropertyBasedTesting\GeneratorMethodThatDoesNotReturnAValueGeneratorTest::integers() does not return a PHPUnit\Framework\PropertyBased\ValueGenerator

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
