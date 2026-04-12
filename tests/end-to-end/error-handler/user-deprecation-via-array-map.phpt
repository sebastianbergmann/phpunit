--TEST--
E_USER_DEPRECATED triggered inside array_map callback handles stack frames without file
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/UserDeprecationViaArrayMapTest.php';

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
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\UserDeprecationViaArrayMapTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\UserDeprecationViaArrayMapTest::testUserDeprecationTriggeredInsideArrayMapCallback)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\UserDeprecationViaArrayMapTest::testUserDeprecationTriggeredInsideArrayMapCallback)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\UserDeprecationViaArrayMapTest::testUserDeprecationTriggeredInsideArrayMapCallback, unknown if issue was triggered in first-party code or third-party code, suppressed using operator) in %sUserDeprecationViaArrayMapTest.php:%d
deprecation from array_map callback
Test Passed (PHPUnit\TestFixture\ErrorHandler\UserDeprecationViaArrayMapTest::testUserDeprecationTriggeredInsideArrayMapCallback)
Test Finished (PHPUnit\TestFixture\ErrorHandler\UserDeprecationViaArrayMapTest::testUserDeprecationTriggeredInsideArrayMapCallback)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\UserDeprecationViaArrayMapTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
