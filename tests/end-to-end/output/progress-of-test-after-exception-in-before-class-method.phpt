--TEST--
phpunit ../_files/ExceptionInBeforeClassMethodTest.php ../_files/basic/SuccessTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/ExceptionInBeforeClassMethodTest.php';
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/SuccessTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E.                                                                  2 / 2 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\ExceptionInBeforeClassMethodTest
Exception: message

%sExceptionInBeforeClassMethodTest.php:%d

ERRORS!
Tests: 2, Assertions: 1, Errors: 1.
