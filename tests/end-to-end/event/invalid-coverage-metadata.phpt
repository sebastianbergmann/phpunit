--TEST--
The right events are emitted in the right order for a test that has invalid code coverage metadata
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcov')) {
    print "skip: this test requires pcov\n";
}
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/invalid-coverage-metadata/phpunit.xml';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;

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
Test Suite Started (%s%etests%eend-to-end%eevent%e_files%einvalid-coverage-metadata%ephpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest::testOne)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest::testOne)
Test Triggered PHPUnit Warning (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest::testOne)
Class "PHPUnit\TestFixture\Event\InvalidCoverageMetadata\This\Does\Not\Exist" is not a valid target for code coverage
Test Finished (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%s%etests%eend-to-end%eevent%e_files%einvalid-coverage-metadata%ephpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
