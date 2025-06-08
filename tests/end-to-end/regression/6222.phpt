--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6222
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = __DIR__ . '/6222/Issue6222Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

F....FSFFS                                                        10 / 10 (100%)

Time: %s, Memory: %s

There were 4 failures:

1) PHPUnit\TestFixture\Issue6222\Issue6222Test::testOne
Failed asserting that false is true.

%sIssue6222Test.php:%d

2) PHPUnit\TestFixture\Issue6222\Issue6222Test::testOneCasePassing with data set #1 (2)
Failed asserting that 2 is identical to 1.

%sIssue6222Test.php:%d

3) PHPUnit\TestFixture\Issue6222\Issue6222Test::testZeroCasesPassing with data set #0 (1)
Failed asserting that 1 is identical to 3.

%sIssue6222Test.php:%d

4) PHPUnit\TestFixture\Issue6222\Issue6222Test::testZeroCasesPassing with data set #1 (2)
Failed asserting that 2 is identical to 3.

%sIssue6222Test.php:%d

--

There were 2 skipped tests:

1) PHPUnit\TestFixture\Issue6222\Issue6222Test::testDependingOnOneCasePassing
This test depends on "PHPUnit\TestFixture\Issue6222\Issue6222Test::testOneCasePassing" to pass

2) PHPUnit\TestFixture\Issue6222\Issue6222Test::testDependingOnZeroCasesPassing
This test depends on "PHPUnit\TestFixture\Issue6222\Issue6222Test::testZeroCasesPassing" to pass

FAILURES!
Tests: 10, Assertions: 8, Failures: 4, Skipped: 2.
