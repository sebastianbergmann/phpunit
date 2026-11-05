--TEST--
A generator registered with #[GeneratorFor] that refers to itself before it makes a choice is reported as an error of the property
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/GeneratorForThatRefersToItselfTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\GeneratorForThatRefersToItselfTest::testGeneratorRegisteredForTheClassOfAParameterRefersToItself
PHPUnit\Framework\PropertyBased\GeneratorRefersToItselfException: Cannot generate objects of class PHPUnit\TestFixture\PropertyBasedTesting\Money: the generator refers to itself before it makes a choice, so it would recurse infinitely, for instance because the generator that is registered for a class with #[GeneratorFor] uses Generators::objects() for the same class

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
