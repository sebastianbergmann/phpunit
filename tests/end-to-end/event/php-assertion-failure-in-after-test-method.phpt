--TEST--
The right events are emitted in the right order for a test that fails because of a failed PHP assertion in an "after test" method
--SKIPIF--
<?php declare(strict_types=1);
if (ini_get('zend.assertions') != 1) {
    print 'skip: zend.assertions=1 is required' . PHP_EOL;
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/PhpAssertionFailureInAfterTestMethodTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\PhpAssertionFailureInAfterTestMethodTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\PhpAssertionFailureInAfterTestMethodTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\PhpAssertionFailureInAfterTestMethodTest::testOne)
After Test Method Called (PHPUnit\TestFixture\Event\PhpAssertionFailureInAfterTestMethodTest::afterTest)
After Test Method Failed (PHPUnit\TestFixture\Event\PhpAssertionFailureInAfterTestMethodTest::afterTest)
assert(false)
After Test Method Finished:
- PHPUnit\TestFixture\Event\PhpAssertionFailureInAfterTestMethodTest::afterTest
Test Failed (PHPUnit\TestFixture\Event\PhpAssertionFailureInAfterTestMethodTest::testOne)
assert(false)
Test Finished (PHPUnit\TestFixture\Event\PhpAssertionFailureInAfterTestMethodTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\PhpAssertionFailureInAfterTestMethodTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
