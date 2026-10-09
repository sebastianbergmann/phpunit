--TEST--
A property whose #[ForAll] closure does not return a ValueGenerator is reported as an error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ForAllClosureThatDoesNotReturnAValueGeneratorTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\ForAllClosureThatDoesNotReturnAValueGeneratorTest::testUsesAClosureThatDoesNotReturnAValueGenerator
PHPUnit\Framework\PropertyBased\InvalidPropertyException: Property PHPUnit\TestFixture\PropertyBasedTesting\ForAllClosureThatDoesNotReturnAValueGeneratorTest::testUsesAClosureThatDoesNotReturnAValueGenerator cannot be checked: the closure that is specified with #[ForAll] for parameter $value does not return a PHPUnit\Framework\PropertyBased\ValueGenerator

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
