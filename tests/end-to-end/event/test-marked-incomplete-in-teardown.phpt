--TEST--
The right events are emitted in the right order for a test that calls markTestIncomplete() in tearDown()
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/MarkedIncompleteInTearDownTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::testSucceeds)
Test Prepared (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::testSucceeds)
After Test Method Called (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::tearDown)
After Test Method Errored (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::tearDown)
markTestIncomplete() must not be called in after-test method PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::tearDown()
After Test Method Finished:
- PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::tearDown
Test Errored (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::testSucceeds)
markTestIncomplete() must not be called in after-test method PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::tearDown()
Test Finished (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::testSucceeds)
Test Preparation Started (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::testFails)
Test Prepared (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::testFails)
Test Failed (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::testFails)
Failed asserting that false is true.
After Test Method Called (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::tearDown)
After Test Method Errored (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::tearDown)
markTestIncomplete() must not be called in after-test method PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::tearDown()
After Test Method Finished:
- PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::tearDown
Test Finished (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest::testFails)
Test Suite Finished (PHPUnit\TestFixture\Event\MarkedIncompleteInTearDownTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
