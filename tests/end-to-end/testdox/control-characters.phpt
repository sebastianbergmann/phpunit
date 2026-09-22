--TEST--
Control characters in user-supplied strings are made visible in the TestDox output
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/../_files/ControlCharactersTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

\u{001B}[2J\u{001B}[Hunexpected output
Time: %s, Memory: %s

Control \u{001B}[8mCharacters
 ✔ Unexpected output
 ✘ Failure message
   │
   │ Failed\u{001B}[2K\u{000D}Everything is fine
   │
   │ %s:%d
   │
 ✘ Data set name with data set "\u{001B}[31mdata set name\u{001B}[0m"
   │
   │ Failed asserting that true is false.
   │
   │ %s:%d
   │
 ↩ Label\u{0007} with \u{001B}[8mcontrol characters

FAILURES!
Tests: 4, Assertions: 3, Failures: 2, Skipped: 1.
