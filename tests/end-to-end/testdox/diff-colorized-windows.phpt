--TEST--
TestDox: Diff; Colorized; Windows
--SKIPIF--
<?php declare(strict_types=1);
if (stripos(\PHP_OS, 'WIN') !== 0) {
    print 'skip: Colorized diff is different on *nix systems.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=always';
$_SERVER['argv'][] = __DIR__ . '/_files/DiffTest.php';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

[4mDiff (PHPUnit\TestFixture\TestDox\Diff)[0m
 [31m✘[0m Something that does not work
   [31m┐[0m
   [31m├[0m [41;37mFailed asserting that two strings are equal.[0m
   [31m├[0m [41;37m--- Expected                                [0m
   [31m├[0m [41;37m+++ Actual                                  [0m
   [31m├[0m [41;37m@@ @@                                       [0m
   [31m├[0m [41;37m 'foo\n                                     [0m
   [31m├[0m [41;37m+baz\n                                      [0m
   [31m├[0m [41;37m bar\n                                      [0m
   [31m├[0m [41;37m-baz\n                                      [0m
   [31m├[0m [41;37m '                                          [0m
   [31m│[0m
   [31m╵[0m %stests[2m%e[22mend-to-end[2m%e[22mtestdox[2m%e[22m_files[2m%e[22mDiffTest.php[2m:[22m[34m%d[0m
   [31m┴[0m

Time: %s, Memory: %s


[37;41mFAILURES![0m
[37;41mTests: 1[0m[37;41m, Assertions: 1[0m[37;41m, Failures: 1[0m[37;41m.[0m
