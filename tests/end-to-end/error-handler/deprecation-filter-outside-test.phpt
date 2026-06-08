--TEST--
A registered deprecation filter marks matching deprecations triggered outside of tests as ignored without affecting others
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecation-filter-outside-test';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sautoload.php)
Event Facade Sealed
Test Runner Triggered Deprecation (issue triggered by PHPUnit calling into third-party code, ignored by filter) in %sPassingTest.php:%d
please ignore this deprecation
Test Runner Triggered Deprecation (issue triggered by PHPUnit calling into third-party code) in %sPassingTest.php:%d
this deprecation must be reported
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DeprecationFilterOutsideTest\PassingTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DeprecationFilterOutsideTest\PassingTest::testOne)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DeprecationFilterOutsideTest\PassingTest::testOne)
Test Passed (PHPUnit\TestFixture\ErrorHandler\DeprecationFilterOutsideTest\PassingTest::testOne)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DeprecationFilterOutsideTest\PassingTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DeprecationFilterOutsideTest\PassingTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
