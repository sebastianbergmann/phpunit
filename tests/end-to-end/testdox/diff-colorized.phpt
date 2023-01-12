--TEST--
TestDox: Diff; Colorized
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=always';
$_SERVER['argv'][] = __DIR__ . '/_files/DiffTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

[4mDiff (PHPUnit\TestFixture\TestDox\Diff)[0m
[31m ✘ [0mSomething that does not work
   [31m┐[0m
   [31m├[0m [41;37mFailed asserting that two strings are equal.[0m
   [31m┊[0m [31m---[2m·[22mExpected[0m
   [31m┊[0m [32m+++[2m·[22mActual[0m
   [31m┊[0m [36m@@ @@[0m
   [31m┊[0m  'foo\n
   [31m┊[0m [32m+baz\n[0m
   [31m┊[0m  bar\n
   [31m┊[0m [31m-baz\n[0m
   [31m┊[0m  '
   [31m│[0m
   [31m│[0m %s[22m_files[2m/[22mDiffTest.php[2m:[22m[34m%d[0m
   [31m┴[0m

[37;41mFAILURES![0m
[37;41mTests: 1[0m[37;41m, Assertions: 1[0m[37;41m, Failures: 1[0m[37;41m.[0m
