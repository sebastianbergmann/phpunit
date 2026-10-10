--TEST--
Every outcome of the final replay of the minimal counterexample of a property is reported, like it is for a test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/FailureInTearDownOfFalsifyingExampleTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\PropertyBasedTesting\FailureInTearDownOfFalsifyingExampleTest::testFailsWhenTheValueIsTooLarge
Failed asserting that 10 is less than 10.

Property falsified after 1 example, counterexample shrunk in 1 step:

    $value = 10;

%sFailureInTearDownOfFalsifyingExampleTest.php:%d

2) PHPUnit\TestFixture\PropertyBasedTesting\FailureInTearDownOfFalsifyingExampleTest::testFailsWhenTheValueIsTooLarge
failure in tearDown()

Property falsified after 1 example, counterexample shrunk in 1 step:

    $value = 10;

%sFailureInTearDownOfFalsifyingExampleTest.php:%d

FAILURES!
Tests: 1, Assertions: 2, Failures: 2.
