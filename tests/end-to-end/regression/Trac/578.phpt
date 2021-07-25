--TEST--
#578: Double printing of trace line for exceptions from notices and warnings
--SKIPIF--
<?php declare(strict_types=1);
if (\PHP_MAJOR_VERSION >= 8) {
    print 'skip: PHP < 8 is required.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/578/Issue578Test.php';

require_once __DIR__ . '/../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

EEE                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There were 3 errors:

1) Issue578Test::testNoticesDoublePrintStackTrace
Invalid error type specified

%sIssue578Test.php:%i

2) Issue578Test::testWarningsDoublePrintStackTrace
Invalid error type specified

%sIssue578Test.php:%i

3) Issue578Test::testUnexpectedExceptionsPrintsCorrectly
Exception: Double printed exception

%sIssue578Test.php:%i

ERRORS!
Tests: 3, Assertions: 0, Errors: 3.
