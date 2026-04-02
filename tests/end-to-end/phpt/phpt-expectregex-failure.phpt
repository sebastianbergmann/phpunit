--TEST--
PHPT runner handles EXPECTREGEX failure
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../_files/phpt-expectregex-failure.phpt');

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) %sphpt-expectregex-failure.phpt
Failed asserting that 'this does not match' matches PCRE pattern "/^completely different pattern [0-9]+$/".

%sphpt-expectregex-failure.phpt:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
