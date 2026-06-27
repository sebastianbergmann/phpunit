--TEST--
phpunit --parallel=2 forwards results in global suite order even when an in-process unit is interspersed among worker units
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

.F.                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelOrdering\SecondOrderingTest::testSecond
Failed asserting that 2 is identical to 1.

%sSecondOrderingTest.php:20

FAILURES!
Tests: 3, Assertions: 3, Failures: 1.
