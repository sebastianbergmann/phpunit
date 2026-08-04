--TEST--
PHPT runner considers a test risky when its SKIPIF section produces unrecognized output
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-skipif-unrecognized-output.phpt';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

R                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) %sphpt-skipif-unrecognized-output.phpt
SKIPIF section produced unrecognized output: unexpected output from a broken skip check

OK, but there were issues!
Tests: 1, Assertions: 1, Risky: 1.
