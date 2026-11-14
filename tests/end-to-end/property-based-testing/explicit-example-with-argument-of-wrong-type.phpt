--TEST--
An explicit example whose argument does not match the type of its parameter falsifies the property with an error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/ExplicitExampleWithArgumentOfWrongTypeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExampleWithArgumentOfWrongTypeTest::testOne
TypeError: PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExampleWithArgumentOfWrongTypeTest::testOne(): Argument #1 ($value) must be of type int, string given, called in %s on line %d

Property falsified by explicit example #0:

    $value = 'one';

%sExplicitExampleWithArgumentOfWrongTypeTest.php:%d

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
