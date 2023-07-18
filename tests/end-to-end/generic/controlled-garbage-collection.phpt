--TEST--
phpunit --configuration=__DIR__.'/../_files/controlled-garbage-collection'
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
$_SERVER['argv'][] = __DIR__.'/../_files/controlled-garbage-collection';

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
Test Runner Disabled Garbage Collection
Test Runner Triggered Garbage Collection
Test Suite Started (%s/phpunit.xml, 2 tests)
Test Suite Started (default, 2 tests)
Test Suite Started (PHPUnit\TestFixture\GarbageCollection\GarbageCollectionTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\GarbageCollection\GarbageCollectionTest::testOne)
Test Prepared (PHPUnit\TestFixture\GarbageCollection\GarbageCollectionTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\GarbageCollection\GarbageCollectionTest::testOne)
Test Finished (PHPUnit\TestFixture\GarbageCollection\GarbageCollectionTest::testOne)
Test Runner Triggered Garbage Collection
Test Preparation Started (PHPUnit\TestFixture\GarbageCollection\GarbageCollectionTest::testTwo)
Test Prepared (PHPUnit\TestFixture\GarbageCollection\GarbageCollectionTest::testTwo)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\GarbageCollection\GarbageCollectionTest::testTwo)
Test Finished (PHPUnit\TestFixture\GarbageCollection\GarbageCollectionTest::testTwo)
Test Runner Triggered Garbage Collection
Test Suite Finished (PHPUnit\TestFixture\GarbageCollection\GarbageCollectionTest, 2 tests)
Test Suite Finished (default, 2 tests)
Test Suite Finished (%s/phpunit.xml, 2 tests)
Test Runner Execution Finished
Test Runner Triggered Garbage Collection
Test Runner Enabled Garbage Collection
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
