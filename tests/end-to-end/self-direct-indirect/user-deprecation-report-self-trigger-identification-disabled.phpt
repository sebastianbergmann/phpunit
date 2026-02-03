--TEST--
The correct warning is reported when trigger identification is disabled but needed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/user-deprecation-report-self-trigger-identification-disabled';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

DD                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) The identification of issue triggers is disabled. However, ignoring self-deprecations, direct deprecations, or indirect deprecations is requested.

OK, but there were issues!
Tests: 2, Assertions: 2, PHPUnit Warnings: 1, Deprecations: 2.
