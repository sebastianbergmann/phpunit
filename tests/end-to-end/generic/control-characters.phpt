--TEST--
Control characters in user-supplied strings are made visible in the default output
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = __DIR__ . '/../_files/ControlCharactersTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

\u{001B}[2J\u{001B}[Hunexpected output
Time: %s, Memory: %s

There were 2 failures:

1) PHPUnit\TestFixture\ControlCharactersTest::testFailureMessage
Failed\u{001B}[2K\u{000D}Everything is fine

%s:%d

2) PHPUnit\TestFixture\ControlCharactersTest::testDataSetName@\u{001B}[31mdata set name\u{001B}[0m with data (true)
Failed asserting that true is false.

%s:%d

--

There was 1 skipped test:

1) PHPUnit\TestFixture\ControlCharactersTest::testTestDoxLabel
Skipped\u{001B}[2K\u{000D}Nothing to see here

FAILURES!
Tests: 4, Assertions: 3, Failures: 2, Skipped: 1.
