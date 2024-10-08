--TEST--
The right events are emitted in the right order for a successful test that has a before-test method that is configured with annotation
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/BeforeTestMethodWithAnnotationTest.php';

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
Test Suite Started (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithAnnotationTest, 1 test)
Test Runner Triggered Deprecation (Metadata found in doc-comment for method PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithAnnotationTest::beforeMethod(). Metadata in doc-comments is deprecated and will no longer be supported in PHPUnit 12. Update your test code to use attributes instead.)
Test Preparation Started (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithAnnotationTest::testOne)
Before Test Method Called (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithAnnotationTest::beforeMethod)
Before Test Method Finished:
- PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithAnnotationTest::beforeMethod
Test Prepared (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithAnnotationTest::testOne)
Test Passed (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithAnnotationTest::testOne)
Test Finished (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithAnnotationTest::testOne)
Test Suite Finished (PHPUnit\DeprecatedAnnotationsTestFixture\BeforeTestMethodWithAnnotationTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
