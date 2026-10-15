--TEST--
phpunit ../_files/ExceptionInAfterClassMethodInChildProcessTest.php ../_files/basic/SuccessTest.php
--FILE--
<?php declare(strict_types=1);
putenv('PHPUNIT_TEST_RUNNER_PROCESS_ID=' . getmypid());

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/ExceptionInAfterClassMethodInChildProcessTest.php';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/SuccessTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

EE.                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There were 2 errors:

1) PHPUnit\TestFixture\ExceptionInAfterClassMethodInChildProcessTest::testOne
Exception: message

%sExceptionInAfterClassMethodInChildProcessTest.php:%d

2) PHPUnit\TestFixture\ExceptionInAfterClassMethodInChildProcessTest::testTwo
Exception: message

%sExceptionInAfterClassMethodInChildProcessTest.php:%d

ERRORS!
Tests: 3, Assertions: 3, Errors: 2.
