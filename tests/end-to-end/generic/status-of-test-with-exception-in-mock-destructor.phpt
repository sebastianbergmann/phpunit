--TEST--
The status of a test whose mock object raises an exception in its destructor is error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/StatusOfTestWithExceptionInMockDestructorTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

E                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\StatusOfTestWithExceptionInMockDestructorTest::testOne
Exception: Some exception.

%sExceptionInMockDestructor.php:%d

ERRORS!
Tests: 1, Assertions: 2, Errors: 1.
