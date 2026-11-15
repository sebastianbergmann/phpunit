--TEST--
phpunit --parallel=2 prints what the tests of a worker write outside of the buffered output of a test, in setUpBeforeClass() and tearDownAfterClass() and to STDOUT and STDERR, where a sequential run prints it
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/AWritesOutputTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/BWritesOutputTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

A: setUpBeforeClass()
A: written to STDOUT by the first test
.A: written to STDERR by the second test
.A: tearDownAfterClass()
B: setUpBeforeClass()
B: written to STDOUT by the first test
.B: written to STDERR by the second test
.                                                                4 / 4 (100%)B: tearDownAfterClass()


Time: %s, Memory: %s

OK (4 tests, 4 assertions)
