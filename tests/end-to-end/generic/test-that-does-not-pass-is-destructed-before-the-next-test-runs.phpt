--TEST--
A test that does not pass is destructed before the next test runs
--INI--
zend.exception_ignore_args=0
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/TestThatDoesNotPassIsDestructedBeforeTheNextTestRunsTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

EF.                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\TestThatDoesNotPassIsDestructedBeforeTheNextTestRunsTest::testErrors
Exception: error

%sTestThatDoesNotPassIsDestructedBeforeTheNextTestRunsTest.php:%d

--

There was 1 failure:

1) PHPUnit\TestFixture\TestThatDoesNotPassIsDestructedBeforeTheNextTestRunsTest::testFails
failure

%sTestThatDoesNotPassIsDestructedBeforeTheNextTestRunsTest.php:%d

ERRORS!
Tests: 3, Assertions: 3, Errors: 1, Failures: 1.
