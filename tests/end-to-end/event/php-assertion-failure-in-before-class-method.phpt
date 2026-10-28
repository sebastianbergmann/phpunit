--TEST--
The right events are emitted in the right order for a test that fails because of a failed PHP assertion in a "before first test" method
--SKIPIF--
<?php declare(strict_types=1);
if (ini_get('zend.assertions') != 1) {
    print 'skip: zend.assertions=1 is required' . PHP_EOL;
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/PhpAssertionFailureInBeforeClassMethodTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\PhpAssertionFailureInBeforeClassMethodTest, 1 test)
Before First Test Method Called (PHPUnit\TestFixture\Event\PhpAssertionFailureInBeforeClassMethodTest::beforeFirstTest)
Before First Test Method Failed (PHPUnit\TestFixture\Event\PhpAssertionFailureInBeforeClassMethodTest::beforeFirstTest)
assert(false)
Before First Test Method Finished:
- PHPUnit\TestFixture\Event\PhpAssertionFailureInBeforeClassMethodTest::beforeFirstTest
Test Suite Finished (PHPUnit\TestFixture\Event\PhpAssertionFailureInBeforeClassMethodTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
