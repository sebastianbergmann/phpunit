--TEST--
phpunit --parallel=2 verifies the expectation of a deprecation against the deprecations that the test itself triggered, not against those of a test that ran before it in the same worker
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/DeprecationExpectationTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

DF                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelDeprecationExpectation\DeprecationExpectationTest::testDoesNotTriggerTheExpectedDeprecation
Expected deprecation with message "deprecation" was not triggered

FAILURES!
Tests: 2, Assertions: 2, Failures: 1, Deprecations: 1.
