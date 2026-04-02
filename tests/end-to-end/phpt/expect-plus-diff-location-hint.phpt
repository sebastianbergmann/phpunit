--TEST--
PHPT EXPECT comparison returns correct location hint for added lines in diff
--SKIPIF--
<?php if(str_contains((string)ini_get('xdebug.mode'), 'develop')) {
print 'skip: xdebug.mode=develop is enabled';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../_files/phpt-expect-plus-first-diff-example.phpt');

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) %sphpt-expect-plus-first-diff-example.phpt
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
 'aaa\n
+inserted\n
 bbb'

%sphpt-expect-plus-first-diff-example.phpt:1

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
