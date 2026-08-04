--TEST--
PHPT runner reports an error for a PHPT file with more than one expectation section
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-conflicting-expectation-sections.phpt';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) %sphpt-conflicting-expectation-sections.phpt
PHPUnit\Runner\Phpt\ConflictingPhptSectionsException: PHPT file must not contain more than one of the sections --EXPECT--, --EXPECTF--

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
