--TEST--
phpunit --parallel=2 emits the warning for an attribute that does not apply to a property once, although the worker that checks the property builds its test suite again
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/IneligibleAttributesTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers
Random Seed:   1234

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Method PHPUnit\TestFixture\ParallelProperty\IneligibleAttributesTest::testIsAnnotatedWithRunInSeparateProcess is annotated with #[RunInSeparateProcess] but is a property and will not be run in a separate process

OK, but there were issues!
Tests: 1, Assertions: 10, PHPUnit Warnings: 1.
