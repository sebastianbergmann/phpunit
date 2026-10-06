--TEST--
phpunit --parallel=2 reports an extension that registers a subscriber of an unknown type in the workers with a test runner warning about its failed bootstrap, as it does for a test that runs in a separate process
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/unknown-subscriber/phpunit.xml';
$_SERVER['argv'][] = '--parallel=2';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %sphpunit.xml
Parallel:      2 workers

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Bootstrapping of extension PHPUnit\TestFixture\ParallelWorkerExtension\UnknownSubscriber\Extension in a parallel worker process failed: Subscriber "PHPUnit\TestFixture\ParallelWorkerExtension\UnknownSubscriber\UnknownSubscriber" does not implement any known interface - did you forget to register it?
%A
OK, but there were issues!
Tests: 2, Assertions: 2, PHPUnit Warnings: 1.
