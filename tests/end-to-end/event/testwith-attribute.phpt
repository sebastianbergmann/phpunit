--TEST--
The right events are emitted in the right order for a successful test that uses the TestWith and TestWithJson attributes
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
$_SERVER['argv'][] = __DIR__ . '/../../_files/Metadata/Attribute/tests/TestWithTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (2 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest, 2 tests)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOne, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOne#0)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOne#0)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOne#0)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOne#0)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testOne, 1 test)
Test Suite Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwo, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwo#0)
Test Prepared (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwo#0)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwo#0)
Test Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwo#0)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest::testTwo, 1 test)
Test Suite Finished (PHPUnit\TestFixture\Metadata\Attribute\TestWithTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
