--TEST--
phpunit --parallel=2 runs tests that return values that cannot be serialized, or cannot be unserialized in the main process, and passes such a value to a test of another class that depends on it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--display-phpunit-notices';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

There were 2 PHPUnit test runner notices:

1) The tests of class PHPUnit\TestFixture\ParallelReturnValues\AClosureProducerTest are run in the main process instead of a parallel worker because test PHPUnit\TestFixture\ParallelReturnValues\BClosureConsumerTest::testReceivesTheClosure depends on PHPUnit\TestFixture\ParallelReturnValues\AClosureProducerTest::testReturnsAClosure, a test of this class

2) The tests of class PHPUnit\TestFixture\ParallelReturnValues\BClosureConsumerTest are run in the main process instead of a parallel worker because test testReceivesTheClosure depends on PHPUnit\TestFixture\ParallelReturnValues\AClosureProducerTest::testReturnsAClosure, a test of another class

OK, but there were issues!
Tests: 4, Assertions: 4, PHPUnit Notices: 2.
