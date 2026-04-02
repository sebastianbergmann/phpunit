--TEST--
PHPT runner handles parse error when FILE section is missing
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../_files/phpt-invalid-no-file-section.phpt');

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) %sphpt-invalid-no-file-section.phpt
PHPUnit\Runner\Phpt\InvalidPhptFileException%s

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
