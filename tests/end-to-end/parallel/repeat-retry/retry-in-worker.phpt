--TEST--
phpunit --parallel=2 retries a test method annotated with #[Retry] inside the worker that runs its class
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/FlakyTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 retried test:

1) PHPUnit\TestFixture\ParallelRepeatRetry\FlakyTest::testFlaky
1 failed attempt

OK (2 tests, 2 assertions)
