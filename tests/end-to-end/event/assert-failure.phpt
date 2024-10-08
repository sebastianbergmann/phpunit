--TEST--
The right events are emitted in the right order for a test that fails because of assert()
--SKIPIF--
<?php declare(strict_types=1);
if (ini_get('zend.assertions') != 1) {
    print 'skip: zend.assertions=1 is required' . PHP_EOL;
}

if (ini_get('assert.exception') != 1) {
    print 'skip: assert.exception=1 is required' . PHP_EOL;
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/AssertTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\AssertTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\AssertTest::testAssert)
Test Prepared (PHPUnit\TestFixture\Event\AssertTest::testAssert)
Test Failed (PHPUnit\TestFixture\Event\AssertTest::testAssert)
assert(false)
Test Finished (PHPUnit\TestFixture\Event\AssertTest::testAssert)
Test Suite Finished (PHPUnit\TestFixture\Event\AssertTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
