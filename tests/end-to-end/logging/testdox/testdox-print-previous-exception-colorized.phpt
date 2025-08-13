--TEST--
Testdox: print error message
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--display-errors';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=always';
$_SERVER['argv'][] =  __DIR__ . '/../_files/ThrowsWithPreviousExceptionTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s

Time: %s, Memory: %s

[4mThrows With Previous Exception (PHPUnit\TestFixture\ThrowsWithPreviousException)[0m
[33m ✘ [0mFoo
   [33m┐[0m
   [33m├[0m [43;30mException: Outer[0m
   [33m│[0m
   [33m│[0m [2m%sThrowsWithPreviousExceptionTest.php[2m:[22m[34m%d[0m%A
   [33m│[0m Caused by:
   [33m├[0m [43;30mException: Inner[0m
   [33m│[0m
   [33m│[0m [2m%sThrowsWithPreviousExceptionTest.php[2m:[22m[34m%d[0m%A
   [33m┴[0m

[37;41mERRORS![0m
[37;41mTests: 1[0m[37;41m, Assertions: 0[0m[37;41m, Errors: 1[0m[37;41m.[0m
