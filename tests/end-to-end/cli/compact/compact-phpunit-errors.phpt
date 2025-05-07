--TEST--
phpunit --compact ../../_files/compact/PhpunitErrorTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = __DIR__ . '/../../_files/compact/PhpunitErrorTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

...                                                                 3 / 3 (100%)

Time: 00:00, Memory: 14.00 MB

There were 2 PHPUnit errors:

1) PHPUnit\TestFixture\TestCompactResultPrinter\PhpunitErrorTest

ERRORS!
Tests: 3, Assertions: 3, Errors: 2.
