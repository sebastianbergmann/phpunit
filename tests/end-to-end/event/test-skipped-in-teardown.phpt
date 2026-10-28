--TEST--
The right events are emitted in the right order for a test skipped in tearDown()
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/SkippedInTearDownTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\SkippedInTearDownTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\SkippedInTearDownTest::testSucceeds)
Test Prepared (PHPUnit\TestFixture\Event\SkippedInTearDownTest::testSucceeds)
After Test Method Called (PHPUnit\TestFixture\Event\SkippedInTearDownTest::tearDown)
Test Triggered PHPUnit Deprecation (PHPUnit\TestFixture\Event\SkippedInTearDownTest::testSucceeds)
Calling markTestSkipped() in after-test method PHPUnit\TestFixture\Event\SkippedInTearDownTest::tearDown() is deprecated and will no longer be possible in PHPUnit 14.
After Test Method Finished:
- PHPUnit\TestFixture\Event\SkippedInTearDownTest::tearDown
Test Skipped (PHPUnit\TestFixture\Event\SkippedInTearDownTest::testSucceeds)
message
Test Finished (PHPUnit\TestFixture\Event\SkippedInTearDownTest::testSucceeds)
Test Preparation Started (PHPUnit\TestFixture\Event\SkippedInTearDownTest::testFails)
Test Prepared (PHPUnit\TestFixture\Event\SkippedInTearDownTest::testFails)
Test Failed (PHPUnit\TestFixture\Event\SkippedInTearDownTest::testFails)
Failed asserting that false is true.
After Test Method Called (PHPUnit\TestFixture\Event\SkippedInTearDownTest::tearDown)
Test Triggered PHPUnit Deprecation (PHPUnit\TestFixture\Event\SkippedInTearDownTest::testFails)
Calling markTestSkipped() in after-test method PHPUnit\TestFixture\Event\SkippedInTearDownTest::tearDown() is deprecated and will no longer be possible in PHPUnit 14.
After Test Method Finished:
- PHPUnit\TestFixture\Event\SkippedInTearDownTest::tearDown
Test Finished (PHPUnit\TestFixture\Event\SkippedInTearDownTest::testFails)
Test Suite Finished (PHPUnit\TestFixture\Event\SkippedInTearDownTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
