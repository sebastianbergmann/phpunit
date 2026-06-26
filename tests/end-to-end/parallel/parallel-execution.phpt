--TEST--
phpunit --parallel=2 runs each test class in a worker process and forwards the results in suite order
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

...F                                                                4 / 4 (100%)

Time: %s, Memory: %s

First Parallel (PHPUnit\TestFixture\ParallelExecution\FirstParallel)
 ✔ One
 ✔ Two

Second Parallel (PHPUnit\TestFixture\ParallelExecution\SecondParallel)
 ✔ Three
 ✘ Four
   │
   │ Failed asserting that 5 is identical to 4.
   │
   │ %sSecondParallelTest.php:23
   │

FAILURES!
Tests: 4, Assertions: 4, Failures: 1.
