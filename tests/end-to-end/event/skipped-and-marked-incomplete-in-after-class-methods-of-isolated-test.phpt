--TEST--
Calling markTestSkipped() or markTestIncomplete() in an "after last test" method is ignored and triggers a PHPUnit deprecation when the tests of the test class are run in process isolation
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::testOne)
Child Process Started (test requiring process isolation)
Child Process Finished (test requiring process isolation)
Test Prepared (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::testOne)
Test Triggered PHPUnit Deprecation (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::testOne)
Calling markTestSkipped() in after-last-test method PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::skip() is deprecated and will no longer be possible in PHPUnit 14.
Test Triggered PHPUnit Deprecation (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::testOne)
Calling markTestIncomplete() in after-last-test method PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::markIncomplete() is deprecated and will no longer be possible in PHPUnit 14.
Test Passed (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
