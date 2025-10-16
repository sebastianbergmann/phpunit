--TEST--
phpunit ../../_files/ExceptionInMockDestructorTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/ExceptionInMockDestructorTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\ExceptionInMockDestructorTest::testOne
Exception: Some exception.

%sExceptionInMockDestructor.php:%d

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
