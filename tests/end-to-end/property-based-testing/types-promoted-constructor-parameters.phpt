--TEST--
The type in the @var tag of a promoted constructor parameter is used for the parameter unless the constructor has a @param tag for it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/PromotedConstructorParameterTypesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

..F                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\PromotedConstructorParameterTypesTest::testIsFalsifiedByAProductThatTheVarTagDoesNotAllow
Failed asserting that an array contains %s.

Property falsified after %s:

    $rating = new Rating(stars: 1, product: %s, tags: [], verified: false);

%sPromotedConstructorParameterTypesTest.php:%d

FAILURES!
Tests: 3, Assertions: %d, Failures: 1.
