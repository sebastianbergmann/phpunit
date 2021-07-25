--TEST--
PHPT runner supports XFAIL section
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--verbose';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../_files/phpt-unsupported-section.phpt');

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

S                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 skipped test:

1) %stests%e_files%ephpt-unsupported-section.phpt
PHPUnit does not support PHPT GET sections

OK, but incomplete, skipped, or risky tests!
Tests: 1, Assertions: 1, Skipped: 1.
