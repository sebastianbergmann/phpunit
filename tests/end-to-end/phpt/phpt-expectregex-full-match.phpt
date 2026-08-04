--TEST--
PHPT runner rejects output that only partially matches an EXPECTREGEX section
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-expectregex-substring.phpt';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) %sphpt-expectregex-substring.phpt
Failed asserting that 'prefix match 123 suffix' matches PCRE pattern "/^match [0-9]+$/s".

%sphpt-expectregex-substring.phpt:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
