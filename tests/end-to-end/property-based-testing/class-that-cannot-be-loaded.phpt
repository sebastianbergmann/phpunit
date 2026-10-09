--TEST--
A property for a parameter of which no generator can be derived because its class cannot be loaded is reported as a PHPUnit error, and the other tests are run
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = __DIR__ . '/_files/ClassThatCannotBeLoadedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit error:

1) PHPUnit\TestFixture\PropertyBasedTesting\ClassThatCannotBeLoadedTest::testParameterIsOfClassThatCannotBeLoaded
Property PHPUnit\TestFixture\PropertyBasedTesting\ClassThatCannotBeLoadedTest::testParameterIsOfClassThatCannotBeLoaded cannot be checked: cannot derive a generator for parameter $object, Error was raised: Class "PHPUnit\TestFixture\PropertyBasedTesting\ParentClassThatDoesNotExist" not found

%sClassThatCannotBeLoadedTest.php:%d

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
