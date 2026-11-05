--TEST--
A property is considered risky when fewer of its examples than #[RequireClassification] requires are classified with a label, unless it is falsified
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/RequiredClassificationsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

RF                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\RequiredClassificationsTest::testIsFalsified
Failed asserting that 5 is less than 5.

Property falsified after %s, counterexample shrunk in %s:

    $value = 5;

%sRequiredClassificationsTest.php:%d

--

There was 1 risky test:

1) PHPUnit\TestFixture\PropertyBasedTesting\RequiredClassificationsTest::testClassifiesTooFewExamplesAsZero
Only 10.0% of the examples of this property were classified as "zero", but at least 20.0% are required

%sRequiredClassificationsTest.php:%d

FAILURES!
Tests: 2, Assertions: %d, Failures: 1, Risky: 1.
