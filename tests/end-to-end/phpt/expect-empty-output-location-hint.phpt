--TEST--
PHPT EXPECT comparison returns fallback location hint for empty output
--SKIPIF--
<?php if(str_contains((string)ini_get('xdebug.mode'), 'develop')) {
print 'skip: xdebug.mode=develop is enabled';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../_files/phpt-empty-output-failure.phpt');

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) %sphpt-empty-output-failure.phpt
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'expected output'
+''

%sphpt-empty-output-failure.phpt:6

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
