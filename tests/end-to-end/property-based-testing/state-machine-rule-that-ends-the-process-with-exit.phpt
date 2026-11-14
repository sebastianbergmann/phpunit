--TEST--
A rule of a state machine that ends the PHP process with exit() is reported with the steps that were taken before it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = __DIR__ . '/_files/StateMachineThatEndsTheProcessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234
%A
Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\PropertyBasedTesting\StateMachineThatEndsTheProcessTest::testExitsInRule.

The PHP process ended while this example of the property was run:

%A    $machine->increment(by: %d);
    $machine->increment(by: %d);
