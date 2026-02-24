--TEST--
Errors matching baseline entries are reported as ignored by baseline
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/baseline';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sSource.php)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (%sphpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\Baseline\SourceTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\Baseline\SourceTest::testDeprecation)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\Baseline\SourceTest::testDeprecation)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\Baseline\SourceTest::testDeprecation, issue triggered by test code calling into first-party code, ignored by baseline) in %s:%d
baseline deprecation
Test Passed (PHPUnit\TestFixture\ErrorHandler\Baseline\SourceTest::testDeprecation)
Test Finished (PHPUnit\TestFixture\ErrorHandler\Baseline\SourceTest::testDeprecation)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\Baseline\SourceTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%sphpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
