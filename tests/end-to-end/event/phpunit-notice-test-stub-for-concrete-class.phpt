--TEST--
The right events are emitted in the right order for a test that creates a test stub for a concrete class
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/TestStubForConcreteClassTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (1 test)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\TestStubForConcreteClassTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\TestStubForConcreteClassTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\TestStubForConcreteClassTest::testOne)
Test Stub Created (PHPUnit\TestFixture\Event\ExtendableClass)
Test Triggered PHPUnit Notice (PHPUnit\TestFixture\Event\TestStubForConcreteClassTest::testOne)
Consider doubling interfaces instead of concrete classes such as PHPUnit\TestFixture\Event\ExtendableClass
Assertion Succeeded (Constraint: is false, Value: false)
Test Passed (PHPUnit\TestFixture\Event\TestStubForConcreteClassTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\TestStubForConcreteClassTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\TestStubForConcreteClassTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
