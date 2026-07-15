--TEST--
phpunit --parallel=2 skips a test that depends on a failing test of another class, exactly as a sequential run does
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/FailingProducerTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/SkippedConsumerTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

FS                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelCrossClassDepends\FailingProducerTest::testProduces
Failed asserting that false is true.

%sFailingProducerTest.php:%d

FAILURES!
Tests: 2, Assertions: 1, Failures: 1, Skipped: 1.
