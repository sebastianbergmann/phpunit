--TEST--
With no source configuration, deprecation trigger is reported as unknown
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/NoSourceDeprecationTest.php';

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
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\NoSourceDeprecationTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\NoSourceDeprecationTest::testDeprecationWithoutSource)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\NoSourceDeprecationTest::testDeprecationWithoutSource)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\NoSourceDeprecationTest::testDeprecationWithoutSource, unknown if issue was triggered in first-party code or third-party code) in %s:%d
deprecation without source
Test Passed (PHPUnit\TestFixture\ErrorHandler\NoSourceDeprecationTest::testDeprecationWithoutSource)
Test Finished (PHPUnit\TestFixture\ErrorHandler\NoSourceDeprecationTest::testDeprecationWithoutSource)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\NoSourceDeprecationTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
