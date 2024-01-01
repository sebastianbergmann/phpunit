--TEST--
PHPT EXPECT comparison returns correct code location hint
--SKIPIF--
<?php if(str_contains((string)ini_get('xdebug.mode'), 'develop')) {
print 'skip: xdebug.mode=develop is enabled';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../_files/phpt-expect-location-hint-example.phpt');

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) %stests%eend-to-end%e_files%ephpt-expect-location-hint-example.phpt
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'Nothing to see here, move along'
+'Fatal error: Uncaught Error: Call to undefined function some_unknown_function() in %s:2\n
+Stack trace:\n
+#0 {main}\n
+  thrown in %s on line 2'

%stests%eend-to-end%e_files%ephpt-expect-location-hint-example.phpt:9

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
