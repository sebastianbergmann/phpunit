--TEST--
The generators of a property whose requirements are met only when it runs are set up when it runs
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/RequirementMetAtRunTimeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

.E                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\RequirementMetAtRunTimeTest::testHasAParameterForWhichNoGeneratorCanBeDerived
PHPUnit\Framework\PropertyBased\InvalidPropertyException: Property PHPUnit\TestFixture\PropertyBasedTesting\RequirementMetAtRunTimeTest::testHasAParameterForWhichNoGeneratorCanBeDerived cannot be checked: cannot derive a generator for parameter $callable of type callable: callable is not supported

ERRORS!
Tests: 2, Assertions: 5, Errors: 1.
