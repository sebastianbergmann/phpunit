--TEST--
https://github.com/sebastianbergmann/phpunit/issues/5287
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
$_SERVER['argv'][] = __DIR__ . '/5287';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (3 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (%s/5287, 3 tests)
Test Suite Started (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test#0)
Test Prepared (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test#0)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test#0)
Test Finished (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test#0)
Test Suite Finished (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest::test, 1 test)
Test Suite Finished (PHPUnit\TestFixture\Issue5278\A\AnotherClassTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Issue5278\B\MyClassTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Issue5278\B\MyClassTest::test)
Test Prepared (PHPUnit\TestFixture\Issue5278\B\MyClassTest::test)
Assertion Failed (Constraint: is true, Value: false)
Test Failed (PHPUnit\TestFixture\Issue5278\B\MyClassTest::test)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Issue5278\B\MyClassTest::test)
Test Suite Finished (PHPUnit\TestFixture\Issue5278\B\MyClassTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Issue5278\C\MyClassTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Issue5278\C\MyClassTest::test)
Test Prepared (PHPUnit\TestFixture\Issue5278\C\MyClassTest::test)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Issue5278\C\MyClassTest::test)
Test Finished (PHPUnit\TestFixture\Issue5278\C\MyClassTest::test)
Test Suite Finished (PHPUnit\TestFixture\Issue5278\C\MyClassTest, 1 test)
Test Suite Finished (%s/5287, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
