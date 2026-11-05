--TEST--
phpunit --parallel=2 runs a test class whose tests are all skipped when the bootstrap script registers an error handler that turns every warning into an exception
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/bootstrap.php';
$_SERVER['argv'][] = __DIR__ . '/_files/tests/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

S.                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK, but some tests were skipped!
Tests: 2, Assertions: 1, Skipped: 1.
