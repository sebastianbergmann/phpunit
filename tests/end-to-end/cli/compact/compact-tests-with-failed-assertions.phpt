--TEST--
phpunit --compact ../../_files/compact/ErrorTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = __DIR__ . '/../../_files/compact/FailureTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F.F                                                                 3 / 3 (100%)

Time: 00:00.001, Memory: 14.00 MB

There was 1 failure:

1) PHPUnit\TestFixture\TestCompactResultPrinter\FailureTest

FAILURES!
Tests: 3, Assertions: 3, Failures: 2.
