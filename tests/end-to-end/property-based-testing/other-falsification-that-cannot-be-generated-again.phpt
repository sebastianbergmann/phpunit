--TEST--
Another way in which a property was falsified while its counterexample was shrunk is not reported when its counterexample cannot be generated again
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/OtherFalsificationThatCannotBeGeneratedAgainTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\PropertyBasedTesting\OtherFalsificationThatCannotBeGeneratedAgainTest::testIsFalsifiedInAnotherWayByAValueThatCannotBeGeneratedAgain
Failed asserting that 500 is less than 500.

Property falsified after %s, counterexample shrunk in %s:

    $value = 500;

%sOtherFalsificationThatCannotBeGeneratedAgainTest.php:%d

FAILURES!
Tests: 1, Assertions: %d, Failures: 1.
