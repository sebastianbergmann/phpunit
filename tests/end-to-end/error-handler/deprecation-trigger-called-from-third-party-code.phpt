--TEST--
Deprecation triggered in first-party code using a configured deprecation trigger function is classified as self even when the first-party code was called from third-party code
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecation-trigger-called-from-third-party-code';

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
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerCalledFromThirdPartyCode\DeprecationTriggerTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerCalledFromThirdPartyCode\DeprecationTriggerTest::testDeprecationInFirstPartyCodeCalledFromTestCode)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerCalledFromThirdPartyCode\DeprecationTriggerTest::testDeprecationInFirstPartyCodeCalledFromTestCode)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerCalledFromThirdPartyCode\DeprecationTriggerTest::testDeprecationInFirstPartyCodeCalledFromTestCode, issue triggered by test code calling into first-party code, suppressed using operator) in %sFirstPartyClass.php:16
deprecation in first-party code
Test Passed (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerCalledFromThirdPartyCode\DeprecationTriggerTest::testDeprecationInFirstPartyCodeCalledFromTestCode)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerCalledFromThirdPartyCode\DeprecationTriggerTest::testDeprecationInFirstPartyCodeCalledFromTestCode)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerCalledFromThirdPartyCode\DeprecationTriggerTest::testDeprecationInFirstPartyCodeCalledFromThirdPartyCode)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerCalledFromThirdPartyCode\DeprecationTriggerTest::testDeprecationInFirstPartyCodeCalledFromThirdPartyCode)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerCalledFromThirdPartyCode\DeprecationTriggerTest::testDeprecationInFirstPartyCodeCalledFromThirdPartyCode, issue triggered by third-party code calling into first-party code, suppressed using operator) in %sFirstPartyClass.php:16
deprecation in first-party code
Test Passed (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerCalledFromThirdPartyCode\DeprecationTriggerTest::testDeprecationInFirstPartyCodeCalledFromThirdPartyCode)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerCalledFromThirdPartyCode\DeprecationTriggerTest::testDeprecationInFirstPartyCodeCalledFromThirdPartyCode)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DeprecationTriggerCalledFromThirdPartyCode\DeprecationTriggerTest, 2 tests)
Test Suite Finished (default, 2 tests)
Test Suite Finished (%sphpunit.xml, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
