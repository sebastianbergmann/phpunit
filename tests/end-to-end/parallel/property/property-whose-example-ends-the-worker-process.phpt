--TEST--
phpunit --parallel=2 reports a property whose example ends the worker process that checks it as errored, along with the example, and runs the tests of the other classes
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/crash/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers
Random Seed:   1234

E.                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\ParallelProperty\AEndsTheProcessTest::testEndsTheProcess
The worker process running PHPUnit\TestFixture\ParallelProperty\AEndsTheProcessTest ended unexpectedly

Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\ParallelProperty\AEndsTheProcessTest::testEndsTheProcess.

The PHP process ended while this example of the property was run:

    $value = %d;

ERRORS!
Tests: 2, Assertions: 10, Errors: 1.
