--TEST--
The placeholders in the TestDox text of a property are not replaced, as it is checked for many examples, and #[TestDoxFormatter] does not apply to a property, for which a warning is emitted
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/_files/TestDoxOfPropertyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

Test Dox Of Property (PHPUnit\TestFixture\PropertyBasedTesting\TestDoxOfProperty)
 ✔ Adding $a and $b is commutative
 ✔ Is annotated with test dox formatter

There was 1 PHPUnit test runner warning:

1) Method PHPUnit\TestFixture\PropertyBasedTesting\TestDoxOfPropertyTest::testIsAnnotatedWithTestDoxFormatter is annotated with #[TestDoxFormatter] but is a property and will not be described by its TestDox formatter

OK, but there were issues!
Tests: 2, Assertions: 6, PHPUnit Warnings: 1.
