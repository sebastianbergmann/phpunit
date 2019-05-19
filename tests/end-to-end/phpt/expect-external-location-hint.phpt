--TEST--
PHPT EXPECT_EXTERNAL results in correct code location hint
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--verbose',
    \realpath(__DIR__ . '/../_files/phpt-expect-external-location-hint-example.phpt'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) %stests%eend-to-end%e_files%ephpt-expect-external-location-hint-example.phpt
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'Hello World\n
-This is line two\n
-and this is line three'
+'Hello world\n
+This is line 2\n
+and this is line 3'

%stests%eend-to-end%e_files%eexpect_external.txt:1
%stests%eend-to-end%e_files%ephpt-expect-external-location-hint-example.phpt:9

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
