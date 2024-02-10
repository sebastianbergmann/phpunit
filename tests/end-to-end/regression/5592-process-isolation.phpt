--TEST--
https://github.com/sebastianbergmann/phpunit/pull/5592
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = __DIR__ . '/5592/Issue5592Test.php';

set_exception_handler(static fn () => null);

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.FF.FF                                                              6 / 6 (100%)

Time: %s, Memory: %s

There were 4 failures:

1) PHPUnit\TestFixture\Issue5592Test::testAddedErrorHandler
Failed asserting that false is true.

%sIssue5592Test.php:%i

2) PHPUnit\TestFixture\Issue5592Test::testRemovedErrorHandler
Failed asserting that false is true.

%sIssue5592Test.php:%i

3) PHPUnit\TestFixture\Issue5592Test::testAddedExceptionHandler
Failed asserting that false is true.

%sIssue5592Test.php:%i

4) PHPUnit\TestFixture\Issue5592Test::testRemovedExceptionHandler
Failed asserting that false is true.

%sIssue5592Test.php:%i

--

There was 1 risky test:

1) PHPUnit\TestFixture\Issue5592Test::testRemovedErrorHandler
Test code or tested code removed error handlers other than its own

%sIssue5592Test.php:%i

FAILURES!
Tests: 6, Assertions: 6, Failures: 4, Risky: 1.
