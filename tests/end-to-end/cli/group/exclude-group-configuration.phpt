--TEST--
phpunit --exclude-group one,two
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/groups';
$_SERVER['argv'][] = '--exclude-group';
$_SERVER['argv'][] = 'one,two';

require_once __DIR__ . '/../../../bootstrap.php';

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
Test Suite Filtered (1 test)
Test Runner Execution Started (1 test)
Test Suite Started (%s/tests/end-to-end/_files/groups/phpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\Groups\FooTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Groups\FooTest::testThree)
Test Prepared (PHPUnit\TestFixture\Groups\FooTest::testThree)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Groups\FooTest::testThree)
Test Finished (PHPUnit\TestFixture\Groups\FooTest::testThree)
Test Suite Finished (PHPUnit\TestFixture\Groups\FooTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%s/tests/end-to-end/_files/groups/phpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
