--TEST--
Control characters in user-supplied strings are made visible in the colorized TestDox output
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=always';
$_SERVER['argv'][] = __DIR__ . '/../_files/ControlCharactersTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

\u{001B}[2J\u{001B}[Hunexpected output
Time: %s, Memory: %s

[4mControl \u{001B}[8mCharacters[0m
[32m ✔ [0mUnexpected output
[31m ✘ [0mFailure message
   [31m┐[0m
   [31m├[0m [41;37mFailed\u{001B}[2K\u{000D}Everything is fine[0m
   [31m│[0m
   [31m│[0m %s[22m_files[2m%e[22mControlCharactersTest.php[2m:[22m[34m%d[0m
   [31m┴[0m
[31m ✘ [0mData set name[2m with [22m[36m\u{001B}[31mdata[2m·[22mset[2m·[22mname\u{001B}[0m[0m
   [31m┐[0m
   [31m├[0m [41;37mFailed asserting that true is false.[0m
   [31m│[0m
   [31m│[0m %s[22m_files[2m%e[22mControlCharactersTest.php[2m:[22m[34m%d[0m
   [31m┴[0m
[36m ↩ [0mLabel\u{0007} with \u{001B}[8mcontrol characters

[37;41mFAILURES![0m
[37;41mTests: 4[0m[37;41m, Assertions: 3[0m[37;41m, Failures: 2[0m[37;41m, Skipped: 1[0m[37;41m.[0m
