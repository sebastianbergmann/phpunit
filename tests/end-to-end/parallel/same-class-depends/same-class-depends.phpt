--TEST--
phpunit --parallel=2 runs a test that depends on a test of the same class in a worker as a sequential run does
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/SameClassDependsTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

..FS......SE                                                      12 / 12 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\ParallelSameClassDepends\SameClassDependsTest::testConsumerOfTestThatDoesNotExist
This test depends on "PHPUnit\TestFixture\ParallelSameClassDepends\SameClassDependsTest::testThatDoesNotExist" which does not exist

--

There was 1 failure:

1) PHPUnit\TestFixture\ParallelSameClassDepends\SameClassDependsTest::testFailingProducer
failure

%sSameClassDependsTest.php:%d

ERRORS!
Tests: 12, Assertions: 9, Errors: 1, Failures: 1, Skipped: 2.
