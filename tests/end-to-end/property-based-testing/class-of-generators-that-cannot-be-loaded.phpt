--TEST--
A property whose generator for a parameter is specified with #[ForAllExternal] as a method of a class that cannot be loaded, or that uses a class that cannot be loaded with #[UseGenerators], is reported as invalid, and the other tests are run
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = __DIR__ . '/_files/ClassOfGeneratorsThatCannotBeLoadedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There were 2 PHPUnit errors:

1) PHPUnit\TestFixture\PropertyBasedTesting\ClassOfGeneratorsThatCannotBeLoadedTest::testSpecifiesTheGeneratorForAParameterWithAMethodOfTheClass
Property PHPUnit\TestFixture\PropertyBasedTesting\ClassOfGeneratorsThatCannotBeLoadedTest::testSpecifiesTheGeneratorForAParameterWithAMethodOfTheClass cannot be checked: the generator for parameter $value is specified as a method of PHPUnit\TestFixture\PropertyBasedTesting\ClassWithParentClassThatDoesNotExist, which cannot be loaded, Error was raised: Class "PHPUnit\TestFixture\PropertyBasedTesting\ParentClassThatDoesNotExist" not found

%sClassOfGeneratorsThatCannotBeLoadedTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\ClassOfGeneratorsThatCannotBeLoadedTest::testUsesTheGeneratorsThatTheClassRegisters
Property PHPUnit\TestFixture\PropertyBasedTesting\ClassOfGeneratorsThatCannotBeLoadedTest::testUsesTheGeneratorsThatTheClassRegisters cannot be checked: #[UseGenerators] is used with PHPUnit\TestFixture\PropertyBasedTesting\ClassWithParentClassThatDoesNotExist, which cannot be loaded, Error was raised: Class "PHPUnit\TestFixture\PropertyBasedTesting\ParentClassThatDoesNotExist" not found

%sClassOfGeneratorsThatCannotBeLoadedTest.php:%d

ERRORS!
Tests: 1, Assertions: 1, Errors: 2.
