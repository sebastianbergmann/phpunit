--TEST--
PHPT runner reports an error for a PHPT file with a duplicated section
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-duplicate-section.phpt';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) %sphpt-duplicate-section.phpt
PHPUnit\Runner\Phpt\DuplicatePhptSectionException: --EXPECT-- section occurs more than once

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
