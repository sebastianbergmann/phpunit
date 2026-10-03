--TEST--phpunit --parallel=2 runs a test that depends on a test of another class in the main process, where it receives the depended-upon test's return value, and reports the class with a test runner notice that names the dependency
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--display-phpunit-notices';
$_SERVER['argv'][] = __DIR__ . '/_files/ProducerTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/ConsumerTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There were 2 PHPUnit test runner notices:

1) The tests of class PHPUnit\TestFixture\ParallelCrossClassDepends\ProducerTest are run in the main process instead of a parallel worker because test PHPUnit\TestFixture\ParallelCrossClassDepends\ConsumerTest::testConsumes depends on PHPUnit\TestFixture\ParallelCrossClassDepends\ProducerTest::testProduces, a test of this class

2) The tests of class PHPUnit\TestFixture\ParallelCrossClassDepends\ConsumerTest are run in the main process instead of a parallel worker because test testConsumes depends on PHPUnit\TestFixture\ParallelCrossClassDepends\ProducerTest::testProduces, a test of another class

OK, but there were issues!
Tests: 2, Assertions: 3, PHPUnit Notices: 2.
