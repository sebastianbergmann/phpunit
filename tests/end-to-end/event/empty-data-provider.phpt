--TEST--
The right events are emitted in the right order for a test that uses a data provider that returns an empty array
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
$_SERVER['argv'][] = __DIR__ . '/_files/EmptyDataProviderTest.php';

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main(false);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
Test Runner Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (1 test)
Test Suite Sorted
Event Facade Sealed
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\EmptyDataProviderTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Event\EmptyDataProviderTest::testCase, 1 test)
Test Prepared (PHPUnit\TestFixture\Event\EmptyDataProviderTest::testCase)
Test Errored (PHPUnit\TestFixture\Event\EmptyDataProviderTest::testCase)
The data provider specified for PHPUnit\TestFixture\Event\EmptyDataProviderTest::testCase is invalid
Empty data set provided by data provider

Test Finished (PHPUnit\TestFixture\Event\EmptyDataProviderTest::testCase)
Test Suite Finished (PHPUnit\TestFixture\Event\EmptyDataProviderTest::testCase, 1 test)
Test Suite Finished (PHPUnit\TestFixture\Event\EmptyDataProviderTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
