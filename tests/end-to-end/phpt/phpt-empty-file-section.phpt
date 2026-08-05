--TEST--
PHPT runner reports an error when the FILE section is empty
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../_files/phpt-invalid-empty-file-section.phpt');

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) %sphpt-invalid-empty-file-section.phpt
PHPUnit\Runner\Phpt\InvalidPhptFileException: --FILE-- section is empty

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
