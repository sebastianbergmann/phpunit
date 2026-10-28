--TEST--
Calling markTestSkipped() or markTestIncomplete() in an "after last test" method is ignored
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/SkippedAndMarkedIncompleteInAfterClassMethodsTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsTest::testOne)
After Last Test Method Called (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsTest::skip)
After Last Test Method Called (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsTest::markIncomplete)
After Last Test Method Called (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsTest::afterLastTest)
After Last Test Method Finished:
- PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsTest::skip
- PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsTest::markIncomplete
- PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsTest::afterLastTest
Test Suite Finished (PHPUnit\TestFixture\Event\SkippedAndMarkedIncompleteInAfterClassMethodsTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
