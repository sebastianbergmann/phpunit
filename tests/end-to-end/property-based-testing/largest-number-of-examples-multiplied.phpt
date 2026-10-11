--TEST--
Multiplying the largest number of examples of a property does not exceed the largest number of examples
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--property-examples-multiplier';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/_files/LargestNumberOfExamplesTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\LargestNumberOfExamplesTest::testIsFalsifiedByTheFirstExample
falsified

Property falsified after 1 example, counterexample shrunk in %d step%S:

    $value = 0;

%sLargestNumberOfExamplesTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
