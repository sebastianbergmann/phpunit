--TEST--
Calling markTestSkipped() or markTestIncomplete() in an "after last test" method is ignored when the tests of the test class are run in process isolation
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
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
Child Process Started
Test Preparation Started (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::testOne)
Child Process Finished
After Last Test Method Called (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::skip)
After Last Test Method Called (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::markIncomplete)
After Last Test Method Finished:
- PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::skip
- PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest::markIncomplete
Test Suite Finished (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsOfIsolatedTestTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
