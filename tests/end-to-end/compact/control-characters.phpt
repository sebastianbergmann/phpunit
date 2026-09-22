--TEST--
Control characters in user-supplied strings are made visible in the compact output
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = __DIR__ . '/../_files/ControlCharactersTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s


--- OUTPUT: PHPUnit\TestFixture\ControlCharactersTest::testUnexpectedOutput
\u{001B}[2J\u{001B}[Hunexpected output

--- FAILURE: PHPUnit\TestFixture\ControlCharactersTest::testFailureMessage
Failed\u{001B}[2K\u{000D}Everything is fine

%sControlCharactersTest.php:%d

--- FAILURE: PHPUnit\TestFixture\ControlCharactersTest::testDataSetName@\u{001B}[31mdata set name\u{001B}[0m with data (true)
Failed asserting that true is false.

%sControlCharactersTest.php:%d

FAILURES (4 tests, 3 assertions, 2 failures, 1 skipped)

--- SKIPPED: PHPUnit\TestFixture\ControlCharactersTest::testTestDoxLabel
Skipped\u{001B}[2K\u{000D}Nothing to see here
