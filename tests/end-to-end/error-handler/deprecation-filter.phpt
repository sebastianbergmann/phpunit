--TEST--
A registered deprecation filter marks matching deprecations as ignored without affecting others
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecation-filter';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sautoload.php)
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (%sphpunit.xml, 2 tests)
Test Suite Started (default, 2 tests)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DeprecationFilter\DeprecationFilterTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DeprecationFilter\DeprecationFilterTest::testIgnoredDeprecation)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DeprecationFilter\DeprecationFilterTest::testIgnoredDeprecation)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\DeprecationFilter\DeprecationFilterTest::testIgnoredDeprecation, issue triggered by PHPUnit calling into test code, suppressed using operator, ignored by filter) in %s:%d
please ignore this deprecation
Test Passed (PHPUnit\TestFixture\ErrorHandler\DeprecationFilter\DeprecationFilterTest::testIgnoredDeprecation)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DeprecationFilter\DeprecationFilterTest::testIgnoredDeprecation)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DeprecationFilter\DeprecationFilterTest::testReportedDeprecation)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DeprecationFilter\DeprecationFilterTest::testReportedDeprecation)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\DeprecationFilter\DeprecationFilterTest::testReportedDeprecation, issue triggered by PHPUnit calling into test code, suppressed using operator) in %s:%d
this deprecation must be reported
Test Passed (PHPUnit\TestFixture\ErrorHandler\DeprecationFilter\DeprecationFilterTest::testReportedDeprecation)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DeprecationFilter\DeprecationFilterTest::testReportedDeprecation)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DeprecationFilter\DeprecationFilterTest, 2 tests)
Test Suite Finished (default, 2 tests)
Test Suite Finished (%sphpunit.xml, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
