--TEST--
A property whose test case class registers a generator with #[GeneratorFor] for a class that cannot be loaded is reported as invalid
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = __DIR__ . '/_files/GeneratorForClassThatCannotBeLoadedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\PropertyBasedTesting\GeneratorForClassThatCannotBeLoadedTest::testIsChecked
Property PHPUnit\TestFixture\PropertyBasedTesting\GeneratorForClassThatCannotBeLoadedTest::testIsChecked cannot be checked: PHPUnit\TestFixture\PropertyBasedTesting\GeneratorForClassThatCannotBeLoadedTest::objectsOfClassThatCannotBeLoaded() is registered with #[GeneratorFor] for PHPUnit\TestFixture\PropertyBasedTesting\ClassWithParentClassThatDoesNotExist, which cannot be loaded, Error was raised: Class "PHPUnit\TestFixture\PropertyBasedTesting\ParentClassThatDoesNotExist" not found

%sGeneratorForClassThatCannotBeLoadedTest.php:%d

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
