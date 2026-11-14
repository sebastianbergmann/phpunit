--TEST--
A property errors, without rejecting the example or shrinking it, when the constructor of a class whose objects are generated from the types of its parameters raises an exception for the generated arguments
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ConstructorThatRaisesExceptionForGeneratedArgumentsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\ConstructorThatRaisesExceptionForGeneratedArgumentsTest::testObjectOfClassWhoseConstructorValidatesItsArguments
PHPUnit\Framework\PropertyBased\CannotCreateObjectException: The constructor of class PHPUnit\TestFixture\PropertyBasedTesting\ValidatedEmailAddress raised InvalidArgumentException for generated arguments: Not an e-mail address

    new PHPUnit\TestFixture\PropertyBasedTesting\ValidatedEmailAddress(value: %s)

A generator that creates objects of this class with arguments that its constructor accepts can be registered with #[GeneratorFor]

Caused by
InvalidArgumentException: Not an e-mail address

%sValidatedEmailAddress.php:%d

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
