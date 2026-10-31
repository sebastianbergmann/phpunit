--TEST--
Strings that are generated from a regular expression are shrunk to the simplest string that falsifies the property
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/RegularExpressionTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\RegularExpressionTest::testSlugDoesNotContainAHyphen
Failed asserting that true is false.

Property falsified after %s, counterexample shrunk in %d steps:

    $slug = 'a-a';

%sRegularExpressionTest.php:%d

FAILURES!
Tests: 1, Assertions: %d, Failures: 1.
