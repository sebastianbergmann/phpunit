--TEST--
GH-503: assertEquals() Line Ending Differences Are Obscure
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = __DIR__ . '/503/Issue503Test.php';

require __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) Issue503Test::testCompareDifferentLineEndings
Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
 #Warning: Strings contain different line endings!
-'foo
+'foo
 '

%s:%i

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
