--TEST--
The right events are emitted in the right order for a test that has invalid code coverage metadata
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcov')) {
    print "skip: this test requires pcov\n";
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/invalid-coverage-metadata/phpunit.xml';
$_SERVER['argv'][] = '--coverage-text';
$_SERVER['argv'][] = '--debug';

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
Test Suite Started (%s%etests%eend-to-end%eevent%e_files%einvalid-coverage-metadata%ephpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest::testOne)
Test Triggered PHPUnit Warning (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest::testOne)
"PHPUnit\TestFixture\Event\InvalidCoverageMetadata\This\Does\Not\Exist" is not a valid target for code coverage
Test Finished (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\InvalidCoverageMetadata\InvalidCoverageMetadataTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%s%etests%eend-to-end%eevent%e_files%einvalid-coverage-metadata%ephpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
