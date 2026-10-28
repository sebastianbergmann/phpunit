--TEST--
TestDox: Failures in before-first-test methods and after-last-test methods are shown
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = __DIR__ . '/_files/failure-in-before-first-and-after-last-test-method';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

Failure In After Last Test Method (PHPUnit\TestFixture\TestDox\FailureInBeforeFirstAndAfterLastTestMethod\FailureInAfterLastTestMethod)
 ✔ One

These before-first-test methods failed:

1) PHPUnit\TestFixture\TestDox\FailureInBeforeFirstAndAfterLastTestMethod\FailureInBeforeFirstTestMethodTest::setUpBeforeClass
Failed asserting that false is true.

%sFailureInBeforeFirstTestMethodTest.php:%d

These after-last-test methods failed:

1) PHPUnit\TestFixture\TestDox\FailureInBeforeFirstAndAfterLastTestMethod\FailureInAfterLastTestMethodTest::tearDownAfterClass
Failed asserting that false is true.

%sFailureInAfterLastTestMethodTest.php:%d

FAILURES!
Tests: 2, Assertions: 1, Failures: 2.
