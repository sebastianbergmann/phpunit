--TEST--
A test that is skipped in a before-test method and errors in an after-test method is counted once
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/SkippedInSetUpAndErroredInTearDownTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\Generic\SkippedInSetUpAndErroredInTearDownTest::testOne
Exception: message

%sSkippedInSetUpAndErroredInTearDownTest.php:%d

ERRORS!
Tests: 1, Assertions: 0, Errors: 1, Skipped: 1.
